<?php

namespace Acme\UserDiscounts;

use Illuminate\Support\Facades\Facade;

class UserDiscountFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'user-discounts'; // Key for the bound service
    }
}