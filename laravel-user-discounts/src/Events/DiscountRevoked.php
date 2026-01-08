<?php

namespace Acme\UserDiscounts\Events;

use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\UserDiscount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountRevoked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Authenticatable $user,
        public Discount $discount,
        public UserDiscount $userDiscount
    ) {}
}