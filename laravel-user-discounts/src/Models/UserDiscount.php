<?php

namespace Acme\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'discount_id',
        'uses_remaining', // Enforce per-user cap
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class); // Assumes default User model
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}