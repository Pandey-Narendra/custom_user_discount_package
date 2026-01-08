<?php

namespace App\Providers;

use Acme\UserDiscounts\Events\DiscountAssigned;
use App\Listeners\SendDiscountAssignedEmail;
use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Events\DiscountRevoked;
use App\Listeners\SendDiscountAppliedEmail;
use App\Listeners\SendDiscountRevokedEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            \Acme\UserDiscounts\Events\DiscountAssigned::class,
            \App\Listeners\SendDiscountAssignedEmail::class
        );

        Event::listen(
            \Acme\UserDiscounts\Events\DiscountApplied::class,
            \App\Listeners\SendDiscountAppliedEmail::class
        );

        Event::listen(
            \Acme\UserDiscounts\Events\DiscountRevoked::class,
            \App\Listeners\SendDiscountRevokedEmail::class
        );
    }
}