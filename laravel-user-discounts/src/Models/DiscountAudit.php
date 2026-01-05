<?php

namespace Acme\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Acme\UserDiscounts\Database\Factories\DiscountAuditFactory;

class DiscountAudit extends Model
{
    use HasFactory;

    protected $table = 'discount_audits';

    protected $fillable = [
        'user_id',
        'discount_id',
        'action',
        'old_usage',
        'new_usage',
        'applied_at',
        'ip_address',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /**
     * Scope: Filter by action type
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    protected static function newFactory()
    {
        return DiscountAuditFactory::new();
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