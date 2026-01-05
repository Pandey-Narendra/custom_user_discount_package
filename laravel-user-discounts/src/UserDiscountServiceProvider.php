<?php

namespace Acme\UserDiscounts;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class UserDiscountServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        // Bootstrapping logic (e.g., publish migrations, load routes)
    }

    public function register(): void
    {
        // Register bindings, singletons, etc.
    }
}