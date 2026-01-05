<?php

namespace Acme\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Acme\UserDiscounts\Models\UserDiscount;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public $userDiscount;
    public $amount;

    public function __construct(UserDiscount $userDiscount, float $amount)
    {
        $this->userDiscount = $userDiscount;
        $this->amount = $amount;
    }
}