<?php

namespace Acme\UserDiscounts\Events;

use Acme\UserDiscounts\Models\Discount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Authenticatable $user,
        public Discount $discount,
        public float $amount,
        public float $subtotalBeforeThisDiscount
    ) {}
}