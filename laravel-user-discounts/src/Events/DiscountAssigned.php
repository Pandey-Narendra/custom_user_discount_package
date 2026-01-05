<?php

namespace Acme\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Acme\UserDiscounts\Models\UserDiscount;

class DiscountAssigned
{
    use Dispatchable, SerializesModels;

    public $userDiscount;

    public function __construct(UserDiscount $userDiscount)
    {
        $this->userDiscount = $userDiscount;
    }
}