<?php

namespace Acme\UserDiscounts\Tests\Unit;

use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Facades\UserDiscount;
use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Orchestra\Testbench\TestCase;

class DiscountApplicationTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return ['Acme\UserDiscounts\UserDiscountServiceProvider'];
    }

    protected function defineEnvironment($app)
    {
        // Optional: Override config if needed (defaults are fine for this test)
        Config::set('user-discounts', [
            'stacking_order' => [], // Use default ordering (highest percentage first)
            'max_percentage_cap' => 1.0,
            'rounding_precision' => 2,
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        // Create minimal users table
        $this->loadLaravelMigrations(['--database' => 'sqlite']);

        // Or manually create users table
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /** @test */
    public function it_applies_discounts_correctly_with_stacking_and_usage_cap()
    {
        Event::fake([DiscountApplied::class]);

        // Create test user using the Authenticatable test model
        $user = TestUser::create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create two discounts
        $discount1 = Discount::create([
            'name'        => 'First Discount',
            'code'        => 'FIRST20',
            'percentage'  => 20.00,
            'user_limit'  => 1,
            'is_active'   => true,
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        $discount2 = Discount::create([
            'name'        => 'Second Discount',
            'code'        => 'SECOND10',
            'percentage'  => 10.00,
            'user_limit'  => 1,
            'is_active'   => true,
            'starts_at'   => now()->subDay(),
            'ends_at'     => now()->addDay(),
        ]);

        // Assign both discounts
        UserDiscount::assign($user, $discount1);
        UserDiscount::assign($user, $discount2);

        // First application on $100 subtotal
        $result = UserDiscount::apply($user, 100.00);

        // Assert: 20% first = 20.00, then 10% on remaining 80.00 = 8.00 → total 28.00
        $this->assertEquals(28.00, $result['total_discount']);
        $this->assertCount(2, $result['applied']);

        // Higher percentage applied first (default behavior)
        $this->assertEquals('FIRST20', $result['applied'][0]['discount']->code);
        $this->assertEquals(20.00, $result['applied'][0]['amount']);
        $this->assertEquals('SECOND10', $result['applied'][1]['discount']->code);
        $this->assertEquals(8.00, $result['applied'][1]['amount']);

        // Usage counts incremented
        $this->assertEquals(1, $user->fresh()->userDiscounts()->where('discount_id', $discount1->id)->first()->usage_count);
        $this->assertEquals(1, $user->fresh()->userDiscounts()->where('discount_id', $discount2->id)->first()->usage_count);

        // Audits created
        $this->assertEquals(2, DiscountAudit::where('action', 'apply')->count());

        // Events dispatched twice
        Event::assertDispatchedTimes(DiscountApplied::class, 2);

        // Second application — usage cap reached → no discount applied
        $secondResult = UserDiscount::apply($user, 100.00);

        $this->assertEquals(0.00, $secondResult['total_discount']);
        $this->assertCount(0, $secondResult['applied']);

        // Usage counts unchanged
        $this->assertEquals(1, $user->fresh()->userDiscounts()->where('discount_id', $discount1->id)->first()->usage_count);
        $this->assertEquals(1, $user->fresh()->userDiscounts()->where('discount_id', $discount2->id)->first()->usage_count);

        // No additional audits or events
        $this->assertEquals(2, DiscountAudit::where('action', 'apply')->count());
        Event::assertDispatchedTimes(DiscountApplied::class, 2);
    }
}

/**
 * Minimal test User model that implements Authenticatable interface
 * Required because UserDiscountService expects Authenticatable
 */
class TestUser extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationship to user_discounts table
     */
    public function userDiscounts()
    {
        return $this->hasMany(\Acme\UserDiscounts\Models\UserDiscount::class, 'user_id');
    }
}