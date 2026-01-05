<?php

namespace Acme\UserDiscounts\Events;

use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\UserDiscount;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Discount $discount,
        public UserDiscount $userDiscount
    ) {}
}