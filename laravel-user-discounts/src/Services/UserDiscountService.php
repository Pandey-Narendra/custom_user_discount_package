<?php

namespace Acme\UserDiscounts\Services;

use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Events\DiscountAssigned;
use Acme\UserDiscounts\Events\DiscountRevoked;
use Acme\UserDiscounts\Exceptions\UserDiscountException;
use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use Acme\UserDiscounts\Models\UserDiscount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Config;
use App\Models\User;

class UserDiscountService
{
    /**
     * Assign a discount to a user (idempotent)
     */
    public function assign(User $user, Discount $discount): UserDiscount
    {
        // Validate: discount must be active
        if (! $discount->scopeActive(new Discount())->where('id', $discount->id)->exists()) {
            throw new UserDiscountException("Discount '{$discount->code}' is not active or has expired.");
        }

        // Idempotent: check if already assigned and not revoked
        $existing = $user->userDiscounts()
            ->where('discount_id', $discount->id)
            ->notRevoked()
            ->first();

        if ($existing) {
            return $existing; // Already assigned → return existing
        }

        return DB::transaction(function () use ($user, $discount) {
            $userDiscount = UserDiscount::create([
                'user_id'     => $user->id,
                'discount_id' => $discount->id,
                'assigned_at' => now(),
            ]);

            DiscountAudit::create([
                'user_id'     => $user->id,
                'discount_id' => $discount->id,
                'action'      => 'assign',
                'old_usage'   => 0,
                'new_usage'   => 0,
                'applied_at'  => now(),
                'ip_address'  => Request::ip(),
            ]);

            event(new DiscountAssigned($user, $discount, $userDiscount));

            return $userDiscount;
        });
    }

    /**
     * Revoke a discount from a user
     */
    public function revoke(User $user, Discount $discount): void
    {
        $userDiscount = $user->userDiscounts()
            ->where('discount_id', $discount->id)
            ->notRevoked()
            ->first();

        if (! $userDiscount) {
            return; // Already revoked or never assigned → idempotent
        }

        DB::transaction(function () use ($user, $discount, $userDiscount) {
            $userDiscount->update(['revoked_at' => now()]);

            DiscountAudit::create([
                'user_id'     => $user->id,
                'discount_id' => $discount->id,
                'action'      => 'revoke',
                'old_usage'   => $userDiscount->usage_count,
                'new_usage'   => $userDiscount->usage_count,
                'applied_at'  => now(),
                'ip_address'  => Request::ip(),
            ]);

            event(new DiscountRevoked($user, $discount, $userDiscount));
        });
    }

    /**
     * Check if user is eligible for a specific discount
     */
    public function eligibleFor(User $user, Discount $discount): bool
    {
        // Discount must be active
        if (! $discount->scopeActive(new Discount())->where('id', $discount->id)->exists()) {
            return false;
        }

        $assignment = $user->userDiscounts()
            ->where('discount_id', $discount->id)
            ->notRevoked()
            ->first();

        if (! $assignment) {
            return false;
        }

        return $assignment->remaining_uses > 0;
    }

    /**
     * Apply one or more eligible discounts to a subtotal
     * Returns: ['total_discount' => float, 'applied' => collection of applied discounts]
     */
    public function apply(User $user, float $subtotal): array
    {
        if ($subtotal <= 0) {
            return ['total_discount' => 0.0, 'applied' => collect()];
        }

        $eligibleAssignments = $this->getEligibleAssignments($user);

        if ($eligibleAssignments->isEmpty()) {
            return ['total_discount' => 0.0, 'applied' => collect()];
        }

        $totalDiscount = 0.0;
        $applied = collect();

        DB::transaction(function () use ($eligibleAssignments, $subtotal, &$totalDiscount, &$applied) {
            foreach ($eligibleAssignments as $assignment) {
                $assignment->refresh()->lockForUpdate(); // Pessimistic lock

                if ($assignment->remaining_uses <= 0 || $assignment->revoked_at) {
                    continue;
                }

                $discount = $assignment->discount;

                $discountAmount = ($discount->percentage / 100) * $subtotal;

                // Apply global max cap from config
                $maxCap = Config::get('user-discounts.max_percentage_cap', 1.0); // 100%
                $cappedAmount = min($discountAmount, $subtotal * $maxCap);

                // Rounding
                $precision = Config::get('user-discounts.rounding_precision', 2);
                $roundedAmount = round($cappedAmount, $precision);

                if ($roundedAmount > 0) {
                    $assignment->increment('usage_count');

                    $applied->push([
                        'discount' => $discount,
                        'amount'   => $roundedAmount,
                        'usage_before' => $assignment->usage_count - 1,
                        'usage_after'  => $assignment->usage_count,
                    ]);

                    $totalDiscount += $roundedAmount;

                    DiscountAudit::create([
                        'user_id'     => $assignment->user_id,
                        'discount_id' => $discount->id,
                        'action'      => 'apply',
                        'old_usage'   => $assignment->usage_count - 1,
                        'new_usage'   => $assignment->usage_count,
                        'applied_at'  => now(),
                        'ip_address'  => Request::ip(),
                    ]);

                    event(new DiscountApplied(
                        $assignment->user,
                        $discount,
                        $roundedAmount,
                        $subtotal - $totalDiscount + $roundedAmount // remaining before this
                    ));
                }
            }
        });

        return [
            'total_discount' => round($totalDiscount, $precision ?? 2),
            'applied'       => $applied,
        ];
    }

    /**
     * Get ordered eligible user discount assignments
     */
    protected function getEligibleAssignments(User $user): \Illuminate\Database\Eloquent\Collection
    {
        $stackingOrder = Config::get('user-discounts.stacking_order', []); // e.g., ['WELCOME10', 'LOYALTY20']

        $query = $user->userDiscounts()
            ->with('discount')
            ->notRevoked()
            ->whereHas('discount', fn(Builder $q) => $q->active());

        if (!empty($stackingOrder)) {
            $query->join('discounts', 'user_discounts.discount_id', '=', 'discounts.id')
                  ->whereIn('discounts.code', $stackingOrder)
                  ->orderByRaw('FIELD(discounts.code, ?)', [implode("','", $stackingOrder)])
                  ->select('user_discounts.*');
        } else {
            // Default: highest percentage first
            $query->orderByDesc('discounts.percentage');
        }

        return $query->get()->filter(fn($ud) => $ud->remaining_uses > 0);
    }
}