<?php

namespace Acme\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'discount_id',
        'action', // e.g., 'assigned', 'revoked', 'applied'
        'amount_discounted', // For apply
        'meta', // JSON for extra data
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}