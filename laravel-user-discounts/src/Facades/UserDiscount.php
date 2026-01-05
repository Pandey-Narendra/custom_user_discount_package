<?php

namespace Acme\UserDiscounts\Facades;

use Illuminate\Support\Facades\Facade;
use Acme\UserDiscounts\Services\UserDiscountService;

/**
 * @method static \Acme\UserDiscounts\Models\UserDiscount assign(\App\Models\User $user, \Acme\UserDiscounts\Models\Discount $discount)
 * @method static void revoke(\App\Models\User $user, \Acme\UserDiscounts\Models\Discount $discount)
 * @method static bool eligibleFor(\App\Models\User $user, \Acme\UserDiscounts\Models\Discount $discount)
 * @method static array apply(\App\Models\User $user, float $subtotal)
 */
class UserDiscount extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserDiscountService::class;
    }
}