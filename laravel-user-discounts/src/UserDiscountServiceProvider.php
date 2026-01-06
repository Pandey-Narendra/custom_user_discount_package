<?php

namespace Acme\UserDiscounts;

use Acme\UserDiscounts\Facades\UserDiscount;
use Acme\UserDiscounts\Services\UserDiscountService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class UserDiscountServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'user-discounts-migrations');
        }

        $this->publishes([
            __DIR__.'/../config/user-discounts.php' => config_path('user-discounts.php'),
        ], 'user-discounts-config');

        $this->mergeConfigFrom(__DIR__.'/../config/user-discounts.php', 'user-discounts');

        // Register factories for package models (handles custom namespace)
        if (class_exists(Factory::class)) {
            // Factory::guessFactoryNamesUsing(
            //     fn (string $modelName) => 'Acme\\UserDiscounts\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
            // );
        }

        // Optional: Register a listener
        // $this->app->booted(function () {
        //     \Illuminate\Support\Facades\Event::listen(
        //         DiscountAssigned::class,
        //         SendDiscountAssignedNotification::class
        //     );
        // });
    }

    public function register(): void
    {
        $this->app->singleton(UserDiscountService::class, function ($app) {
            return new UserDiscountService();
        });

        $this->app->alias(UserDiscountService::class, 'user-discounts');
    }
}