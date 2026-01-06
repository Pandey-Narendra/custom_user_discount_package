## Laravel User Discounts Package
Packagist Version
License

A reusable Laravel package for managing user-level discounts with deterministic stacking, usage caps, auditing, events, and concurrency safety. It allows assigning discounts to users, checking eligibility, applying them to subtotals (e.g., in e-commerce carts), and revoking them. Features include configurable stacking order, max percentage caps, rounding, and automatic exclusion of expired/inactive discounts.

This package follows Laravel best practices: PSR-4 autoloading, Eloquent models, facades, events, and a publishable config. It's designed for Laravel 10+ and is installable via Composer.


## Features

# User-Specific Discounts: 
    Assign/revoke discounts per user.
# Deterministic Stacking: 
    Apply discounts in a configurable order with caps and rounding.
# Usage Enforcement:
     Per-user limits; concurrency-safe increments.
# Auditing: 
    Logs all actions (assign, revoke, apply) with IP for security.
# Events: 
    Dispatch events for extensibility (e.g., notifications).
# Scopes and Accessors: 
    Easy querying for active/not revoked discounts; remaining uses.
# Idempotency: 
    Safe to call operations multiple times.
# Concurrency Safety: 
    DB transactions + row locking.
# CLI Command:
    php artisan discounts:list for listing discounts.

## Requirements

PHP 8.1+
Laravel 10+
Composer

## Installation

# Install via Composer:
    composer require acme/laravel-user-discounts(If developing locally: composer require acme/laravel-user-discounts

# Publish assets (config, migrations, factories):
    php artisan vendor:publish --provider="Acme\\UserDiscounts\\UserDiscountServiceProvider"
    Or selectively: --tag=user-discounts-config, --tag=user-discounts-migrations, --tag=user-discounts-factories.

# Run migrations to create tables (discounts, user_discounts, discount_audits):
    php artisan migrate
    
# (Recommended) Add the HasUserDiscounts trait to your User model for relationships:PHPuse Acme\UserDiscounts\Traits\HasUserDiscounts;

    class User extends Authenticatable
    {
        use HasUserDiscounts;
        // ...
    }

    The package auto-discovers via Composer's extra.laravel.providers.


## Configuration
    Publish the config file:
        php artisan vendor:publish --tag=user-discounts-config

    Edit config/user-discounts.php:

        PHPreturn [
            'stacking_order' => [],  // Array of discount codes/IDs for application order (e.g., ['WELCOME10', 5])
            'max_percentage_cap' => 0.50,  // Max total discount % (0.5 = 50%)
            'rounding_precision' => 2,  // Decimal places for rounding
            'usage_lock_timeout' => 10,  // Seconds for lock timeout (informational)
        ];

        Changes take effect immediately; no restart needed.
        If not published, defaults are used.

## Usage
    Use the Facade UserDiscount for a clean API. 
    All methods are in Acme\UserDiscounts\Facades\UserDiscount.

## Creating Discounts
    Use the Discount model:
        use Acme\UserDiscounts\Models\Discount;

        $discount = Discount::create([
            'name' => 'Welcome Discount',
            'code' => 'WELCOME10',
            'percentage' => 10.00,
            'user_limit' => 1,  // Max uses per user
            'starts_at' => now()->subDays(1),
            'ends_at' => now()->addMonth(),
            'is_active' => true,
        ]);


## Assigning a Discount
    use App\Models\User;
    use Acme\UserDiscounts\Facades\UserDiscount;

    $user = User::find(1);
    $discount = Discount::find(1);

    UserDiscount::assign($user, $discount);  // Idempotent: Skips if already assigned

## Checking Eligibility
    $isEligible = UserDiscount::eligibleFor($user, $discount);  // Returns bool

## Applying Discounts
    $subtotal = 100.0;
    $result = UserDiscount::apply($user, $subtotal);

    // $result = ['total_discount' => 10.0, 'applied' => collect([['discount' => $discount, 'amount' => 10.0, ...]])]
    $finalTotal = $subtotal - $result['total_discount'];

## Revoking a Discount
    UserDiscount::revoke($user, $discount);  // Idempotent: Skips if already revoked

## Querying

# Active discounts: 
    Discount::active()->get()
# User's active assignments: 
    $user->activeUserDiscounts()
# Audits: 
    DiscountAudit::forAction('apply')->get()

## CLI Command

    List discounts:
        php artisan discounts:list  // All discounts
        php artisan discounts:list --active  // Only active/valid

## How It Works (Detailed Flow)
    The package manages discounts through a service class, backed by Eloquent models and DB tables.

# Database Schema:
    discounts: Stores discount details (name, code, percentage, dates, active status).
    user_discounts: Pivot for assignments (user_id, discount_id, assigned_at, revoked_at, usage_count).
    discount_audits: Logs actions with usage changes and IP.

# Assignment Flow:
    Validate discount is active/valid.
    Check if already assigned (idempotent).
    Create UserDiscount record.
    Audit and fire DiscountAssigned event.

# Eligibility Check:
    Use scopes: Discount active(), assignment notRevoked(), usage < limit via accessor remaining_uses.

# Application Flow:
    Gather eligible assignments (active, not revoked, remaining uses > 0).
    Sort deterministically: By config stacking_order or highest percentage.
    In transaction: Lock row, calculate amount (percentage of subtotal, capped, rounded), increment usage if applicable.
    Audit each and fire DiscountApplied.
    Returns total discount and applied details.

# Revocation Flow:
    Set revoked_at on assignment.
    Audit and fire DiscountRevoked.

# Exclusions and Safety:
    Expired/inactive ignored via scopes.
    Concurrency: DB::transaction + lockForUpdate() prevents race conditions.
    Security: Validation, fillable guards, IP auditing.


# Events allow hooks (e.g., email on assign). All operations are atomic and idempotent.
File Structure and What Each File Does

# Root: 
    laravel-user-discounts/

    composer.json: 
        Package metadata, dependencies (e.g., illuminate/support), autoloading, extras for Laravel auto-discovery.

    CHANGELOG.md:
        Version history.

    README.md: 
        This file – documentation.
    .gitignore: 
        Ignores vendor, logs, etc.

    LICENSE: 
        MIT license text.


# config/
    user-discounts.php: Default config for stacking, caps, rounding.

# database/
    migrations/
        create_discounts_table.php: Schema for discounts table.
        create_user_discounts_table.php: Schema for assignments pivot.
        create_discount_audits_table.php: Schema for audits.

# factories/
    DiscountFactory.php: Factory for seeding/testing Discount models (with states like inactive/expired).


# resources/
    views/: Placeholder for publishable views (empty by default; for future templates).

# src/
    UserDiscountServiceProvider.php: Bootstraps package – registers service/facade, publishes assets, loads views, registers commands.

# Commands/
    DiscountsListCommand.php: Artisan command to list discounts in table format.

# Events/
    DiscountAssigned.php, DiscountRevoked.php, DiscountApplied.php: Event classes with payload (user, discount, etc.).

# Exceptions/
    UserDiscountException.php: Custom exception for validation errors.

# Facades/
    UserDiscount.php: Facade for service API.

# Listeners/
    SendDiscountAssignedNotification.php: Example queueable listener (e.g., for notifications).

# Models/
    
    Discount.php: Model for discounts – scopes (active), relationships (userDiscounts, audits).
    UserDiscount.php: Pivot model – scopes (notRevoked), accessors (remaining_uses, is_usable), relationships.
    DiscountAudit.php: Audit model – scopes (forAction), relationships.

# Services/
    UserDiscountService.php: Core logic – methods for assign, revoke, eligibleFor, apply (with transactions, locking, config usage).

# Traits/
    HasUserDiscounts.php: Trait for User model – adds relationships.


# tests/ (optional, for package dev)
    Integration tests (e.g., installation, workflows) using Testbench/PHPUnit.


## How to Test/Validate
    Test in a Laravel app to confirm functionality.

# Manual Testing (CLI/Tinker)
   Run php artisan tinker for interactive testing. Below are detailed examples for various scenarios. Assume you've seeded a user and discounts (e.g., via factories).

    1) Basic Assign and Eligible Check:

        use App\Models\User;
        use Acme\UserDiscounts\Models\Discount;
        use Acme\UserDiscounts\Facades\UserDiscount;

        $user = User::factory()->create();  // Create a test user
        $discount = Discount::factory()->create(['percentage' => 10.0, 'user_limit' => 2, 'code' => 'TEST10']);

        UserDiscount::assign($user, $discount);  // Assigns and audits
        UserDiscount::eligibleFor($user, $discount);  // Should return true
        $user->userDiscounts()->first()->usage_count;  // Should be 0
        DiscountAudit::latest()->first()->action;  // Should be 'assign'

    2) Apply Discount (Single):

        $result = UserDiscount::apply($user, 100.0);  // Apply to $100 subtotal
        dd($result);  // Expect ['total_discount' => 10.0, 'applied' => collection]
        $user->userDiscounts()->first()->usage_count;  // Now 1
        DiscountAudit::forAction('apply')->count();  // At least 1

    3) Stacking with Multiple Discounts:

        $discount2 = Discount::factory()->create(['percentage' => 20.0, 'code' => 'STACK20']);
        UserDiscount::assign($user, $discount2);

        // Set stacking order in config or via Tinker: Config::set('user-discounts.stacking_order', ['TEST10', 'STACK20']);
        $result = UserDiscount::apply($user, 100.0);
        dd($result);  // Total discount ~28.0 (10% then 20% on remaining, capped if set)
        $result['applied']->pluck('discount.code');  // Should follow order: ['TEST10', 'STACK20']

    4) Usage Cap Enforcement:
       
        // Assume user_limit=1 for $discount
        UserDiscount::apply($user, 100.0);  // Succeeds, usage=1
        UserDiscount::eligibleFor($user, $discount);  // false now
        UserDiscount::apply($user, 100.0);  // Ignores this discount, total_discount=0 if no others

    5) Revoke and Exclusion:

        UserDiscount::revoke($user, $discount);  // Revokes
        UserDiscount::eligibleFor($user, $discount);  // false
        UserDiscount::apply($user, 100.0);  // Should not apply revoked discount
        DiscountAudit::forAction('revoke')->first()->ip_address;  // Check audit 

    6)  Expired/Inactive Exclusion:

        $expired = Discount::factory()->expired()->create();  // Using factory state
        UserDiscount::assign($user, $expired);
        UserDiscount::eligibleFor($user, $expired);  // false
        UserDiscount::apply($user, 100.0);  // Ignores expired
        Discount::active()->count();  // Excludes expired/inactive

    7) Concurrency Simulation (Basic)
        
        // Simulate two applies (in real scenarios, use queues or parallel requests)
        DB::beginTransaction();
        $assignment = $user->userDiscounts()->first()->lockForUpdate();
        // Increment usage in one "thread"
        $assignment->increment('usage_count');
        DB::commit();

        // In another Tinker session or loop: Try apply again – should respect lock/cap
        UserDiscount::apply($user, 100.0);  // If cap=1, second fails

    8) Event Firing:

        use Illuminate\Support\Facades\Event;
        Event::fake();
        UserDiscount::assign($user, $discount);
        Event::assertDispatched(\Acme\UserDiscounts\Events\DiscountAssigned::class);

    9) Audits and Queries:
        $user->activeUserDiscounts()->get();  // List active assignments
        DiscountAudit::all();  // All audits

# Web-Based Testing
    Add routes:
    Route::get('/test-discounts', function () {
        // Call methods, return view with results/audits
    });

    Visit URL, refresh for concurrency.
    Verify: Eligibility, discounts applied (with stacking), audits show IP.

# Automated Tests

    Use package's tests: Run PHPUnit in package root.
    In app: Add feature tests for workflows, assert DB changes, events dispatched.
    Required: Test usage cap on concurrent apply (mock service, assert no exceed).

    If tests pass and manual checks match acceptance (assign/eligible/apply with audits, exclusions, caps, stacking, concurrency), it's working.

# Events
    Dispatched events:

    DiscountAssigned($user, $discount, $userDiscount)
    DiscountRevoked($user, $discount, $userDiscount)
    DiscountApplied($user, $discount, $amount, $subtotalBefore)

    Listen in your EventServiceProvider:
    
        protected $listen = [
            \Acme\UserDiscounts\Events\DiscountAssigned::class => [
                \App\Listeners\SendEmail::class,
            ],
        ];

# Contributing
    Fork, develop in modules, test, PR. Use semantic versioning.
# License
    MIT – see LICENSE for details.