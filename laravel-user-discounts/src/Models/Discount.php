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
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('starts_at', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                     });
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