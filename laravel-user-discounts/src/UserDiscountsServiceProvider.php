<?php

namespace Acme\UserDiscounts;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Acme\UserDiscounts\Events\DiscountAssigned;
use Acme\UserDiscounts\Events\DiscountRevoked;
use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Listeners\AuditDiscountAssigned;
use Acme\UserDiscounts\Listeners\AuditDiscountRevoked;
use Acme\UserDiscounts\Listeners\AuditDiscountApplied;

class UserDiscountsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish migrations and config
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'user-discounts-migrations');

        $this->publishes([
            __DIR__ . '/../config/discounts.php' => config_path('user-discounts.php'),
        ], 'user-discounts-config');

        // Register events and listeners for auditing
        Event::listen(DiscountAssigned::class, AuditDiscountAssigned::class);
        Event::listen(DiscountRevoked::class, AuditDiscountRevoked::class);
        Event::listen(DiscountApplied::class, AuditDiscountApplied::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/discounts.php', 'user-discounts');
    }
}