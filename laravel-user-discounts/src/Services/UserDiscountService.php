<?php

namespace Acme\UserDiscounts\Services;

use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Events\DiscountAssigned;
use Acme\UserDiscounts\Events\DiscountRevoked;
use Acme\UserDiscounts\Exceptions\UserDiscountException;
use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use Acme\UserDiscounts\Models\UserDiscount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use Exception;

class UserDiscountService
{
    /**
     * Assign a discount to a user (idempotent)
     */
    public function assign(Authenticatable $user, Discount $discount): ?UserDiscount
    {
        try {
            // Validate inputs
            if (!$user || !$discount) {
                throw new UserDiscountException('Invalid user or discount provided.');
            }

            // Check if discount is currently active
            try {
                $isActive = Discount::active()->where('id', $discount->id)->exists();
            } catch (Exception $e) {
                Log::warning('Error checking discount active status', ['exception' => $e]);
                $isActive = false;
            }

            if (!$isActive) {
                throw new UserDiscountException("Discount '{$discount->code}' is not active or has expired.");
            }

            // Check for existing assignment (idempotent)
            try {
                $existing = $user->userDiscounts()
                    ->where('discount_id', $discount->id)
                    ->notRevoked()
                    ->first();
            } catch (Exception $e) {
                Log::error('Error checking existing assignment', ['exception' => $e]);
                $existing = null;
            }

            if ($existing) {
                return $existing;
            }

            // Perform assignment in transaction
            return DB::transaction(function () use ($user, $discount) {
                try {
                    $userDiscount = UserDiscount::create([
                        'user_id'     => $user->id,
                        'discount_id' => $discount->id,
                        'assigned_at' => now(),
                    ]);

                    $this->createAudit('assign', $user->id, $discount->id, 0, 0);

                    event(new DiscountAssigned($user, $discount, $userDiscount));

                    return $userDiscount;
                } catch (QueryException $e) {
                    Log::error('Database error during discount assignment', [
                        'user_id' => $user->id,
                        'discount_id' => $discount->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new UserDiscountException('Failed to assign discount due to database error.');
                }
            });

        } catch (UserDiscountException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error('Unexpected error in discount assignment', [
                'user_id' => $user?->id,
                'discount_id' => $discount?->id,
                'exception' => $e
            ]);
            throw new UserDiscountException('An unexpected error occurred while assigning the discount.');
        }
    }

    /**
     * Revoke a discount from a user (idempotent)
     */
    public function revoke(Authenticatable $user, Discount $discount): void
    {
        try {
            if (!$user || !$discount) {
                return;
            }

            try {
                $userDiscount = $user->userDiscounts()
                    ->where('discount_id', $discount->id)
                    ->notRevoked()
                    ->first();
            } catch (Exception $e) {
                Log::warning('Error finding assignment for revocation', ['exception' => $e]);
                return;
            }

            if (!$userDiscount) {
                return;
            }

            DB::transaction(function () use ($user, $discount, $userDiscount) {
                try {
                    $oldUsage = $userDiscount->usage_count;

                    $userDiscount->update(['revoked_at' => now()]);

                    $this->createAudit('revoke', $user->id, $discount->id, $oldUsage, $oldUsage);

                    event(new DiscountRevoked($user, $discount, $userDiscount));
                } catch (Exception $e) {
                    Log::error('Error during revocation', ['exception' => $e]);
                    throw $e;
                }
            });

        } catch (Exception $e) {
            Log::error('Unexpected error in discount revocation', ['exception' => $e]);
        }
    }

    /**
     * Check if user is eligible for a specific discount
     */
    public function eligibleFor(Authenticatable $user, Discount $discount): bool
    {
        try {
            if (!$user || !$discount) {
                return false;
            }

            try {
                $isActive = Discount::active()->where('id', $discount->id)->exists();
            } catch (Exception $e) {
                Log::warning('Error checking discount active status in eligibility', ['exception' => $e]);
                return false;
            }

            if (!$isActive) {
                return false;
            }

            try {
                $assignment = $user->userDiscounts()
                    ->where('discount_id', $discount->id)
                    ->notRevoked()
                    ->first();
            } catch (Exception $e) {
                Log::warning('Error checking assignment in eligibility', ['exception' => $e]);
                return false;
            }

            if (!$assignment) {
                return false;
            }

            return $assignment->remaining_uses > 0;

        } catch (Exception $e) {
            Log::error('Unexpected error in eligibility check', ['exception' => $e]);
            return false;
        }
    }

    /**
     * Apply eligible discounts to a subtotal — with proper sequential stacking
     */
    public function apply(Authenticatable $user, float $subtotal): array
    {
        if ($subtotal <= 0 || !$user) {
            return ['total_discount' => 0.0, 'applied' => collect()];
        }

        try {
            $eligibleAssignments = $this->getEligibleAssignments($user);

            if ($eligibleAssignments->isEmpty()) {
                return ['total_discount' => 0.0, 'applied' => collect()];
            }

            $remainingSubtotal = $subtotal;
            $totalDiscount = 0.0;
            $applied = collect();
            $precision = Config::get('user-discounts.rounding_precision', 2);
            $maxCap = Config::get('user-discounts.max_percentage_cap', 1.0);

            DB::transaction(function () use ($eligibleAssignments, &$remainingSubtotal, &$totalDiscount, &$applied, $precision, $maxCap, $user) {
                foreach ($eligibleAssignments as $assignment) {
                    try {
                        $assignment->refresh()->lockForUpdate();

                        if ($assignment->remaining_uses <= 0 || $assignment->revoked_at) {
                            continue;
                        }

                        $discount = $assignment->discount;

                        if (!$discount) {
                            continue;
                        }

                        // Calculate on CURRENT remaining subtotal
                        $discountAmount = ($discount->percentage / 100) * $remainingSubtotal;
                        $cappedAmount = min($discountAmount, $remainingSubtotal * $maxCap);
                        $roundedAmount = round($cappedAmount, $precision);

                        if ($roundedAmount <= 0) {
                            continue;
                        }

                        $oldUsage = $assignment->usage_count;
                        $assignment->increment('usage_count');
                        $newUsage = $assignment->usage_count;

                        $applied->push([
                            'discount'      => $discount,
                            'amount'        => $roundedAmount,
                            'usage_before'  => $oldUsage,
                            'usage_after'   => $newUsage,
                        ]);

                        $totalDiscount += $roundedAmount;
                        $remainingSubtotal -= $roundedAmount;  // ← Critical: reduce remaining

                        $this->createAudit('apply', $assignment->user_id, $discount->id, $oldUsage, $newUsage);

                        event(new DiscountApplied(
                            $user,
                            $discount,
                            $roundedAmount,
                            $remainingSubtotal + $roundedAmount  // subtotal before this discount
                        ));

                    } catch (Exception $e) {
                        Log::warning('Error applying individual discount', [
                            'assignment_id' => $assignment->id ?? null,
                            'exception' => $e
                        ]);
                        continue;
                    }
                }
            });

            return [
                'total_discount' => round($totalDiscount, $precision),
                'applied'       => $applied,
            ];

        } catch (Exception $e) {
            Log::error('Critical error in discount application', ['exception' => $e]);
            return ['total_discount' => 0.0, 'applied' => collect()];
        }
    }

    /**
     * Get ordered eligible assignments with full error resilience
     */
    protected function getEligibleAssignments(Authenticatable $user): Collection
    {
        try {
            $stackingOrder = Config::get('user-discounts.stacking_order', []);

            $query = $user->userDiscounts()
                ->with('discount')
                ->notRevoked()
                ->whereHas('discount', fn(Builder $q) => $q->active());

            if (!empty($stackingOrder)) {
                $query->join('discounts as stacking_discounts', 'user_discounts.discount_id', '=', 'stacking_discounts.id')
                      ->whereIn('stacking_discounts.code', $stackingOrder)
                      ->orderByRaw('FIELD(stacking_discounts.code, ?)', [implode("','", $stackingOrder)])
                      ->select('user_discounts.*');
            } else {
                $query->join('discounts as fallback_discounts', 'user_discounts.discount_id', '=', 'fallback_discounts.id')
                      ->orderByDesc('fallback_discounts.percentage')
                      ->select('user_discounts.*');
            }

            $assignments = $query->get();

            return $assignments->filter(fn($ud) => $ud && $ud->remaining_uses > 0);

        } catch (Exception $e) {
            Log::error('Error retrieving eligible assignments', ['exception' => $e]);
            return collect();
        }
    }

    /**
     * Safe audit creation — never fails the main operation
     */
    private function createAudit(string $action, int $userId, int $discountId, int $oldUsage, int $newUsage): void
    {
        try {
            DiscountAudit::create([
                'user_id'     => $userId,
                'discount_id' => $discountId,
                'action'      => $action,
                'old_usage'   => $oldUsage,
                'new_usage'   => $newUsage,
                'applied_at'  => now(),
                'ip_address'  => Request::ip() ?: 'unknown',
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to create discount audit', [
                'action' => $action,
                'user_id' => $userId,
                'discount_id' => $discountId,
                'exception' => $e
            ]);
        }
    }
}