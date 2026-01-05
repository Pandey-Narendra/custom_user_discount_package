<?php

namespace Acme\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Acme\UserDiscounts\Database\Factories\UserDiscountFactory;

class UserDiscount extends Model
{
    use HasFactory;

    protected $table = 'user_discounts';

    protected $fillable = [
        'user_id',
        'discount_id',
        'assigned_at',
        'revoked_at',
        'usage_count',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    /**
     * Scope: Only currently active (not revoked) assignments
     */
    public function scopeNotRevoked(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Accessor: Remaining uses for this user (based on discount's user_limit)
     */
    public function getRemainingUsesAttribute(): int
    {
        $limit = $this->discount?->user_limit ?? 1;

        return max(0, $limit - $this->usage_count);
    }

    protected static function newFactory()
    {
        return UserDiscountFactory::new();
    }

    /**
     * Accessor: Check if still usable
     */
    public function getIsUsableAttribute(): bool
    {
        return $this->remaining_uses > 0 && $this->revoked_at === null;
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}