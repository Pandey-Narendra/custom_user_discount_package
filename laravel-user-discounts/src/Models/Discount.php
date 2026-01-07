<?php

namespace Acme\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Acme\UserDiscounts\Database\Factories\DiscountFactory;

class Discount extends Model
{
    use HasFactory;

    protected $table = 'discounts';

    protected $fillable = [
        'name',
        'code',
        'percentage',
        'max_usage',
        'user_limit',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function newFactory()
    {
        return DiscountFactory::new();
    }

    /**
     * Scope: Only active and currently valid discounts
     */
    // public function scopeActive($query)
    // {
    //     return $query->where('is_active', true)
    //                  ->where('starts_at', '<=', now())
    //                  ->where(function ($q) {
    //                      $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
    //                  });
    // }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    });
    }
/**
 * Accessor: Is this discount currently active and valid?
 */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }
    /**
     * Relationships
     */
    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DiscountAudit::class);
    }
}