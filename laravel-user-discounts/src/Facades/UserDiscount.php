<?php

namespace Acme\UserDiscounts\Facades;

use Illuminate\Support\Facades\Facade;

class UserDiscount extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'user-discounts'; // Matches the alias in your service provider
    }
}