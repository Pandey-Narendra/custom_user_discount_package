<?php

namespace Acme\UserDiscounts\Traits;

use Acme\UserDiscounts\Models\UserDiscount;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasUserDiscounts
{
    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function activeUserDiscounts(): HasMany
    {
        return $this->userDiscounts()->notRevoked()->with('discount');
    }
}