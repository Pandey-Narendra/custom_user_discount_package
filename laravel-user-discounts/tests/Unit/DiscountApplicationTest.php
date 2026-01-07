<?php

namespace Acme\UserDiscounts\Tests\Unit;

use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Facades\UserDiscount;
use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
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
        Config::set('user-discounts', [
            'stacking_order' => [],
            'max_percentage_cap' => 1.0,
            'rounding_precision' => 2,
        ]);

        // Use our TestUser model
        Config::set('auth.providers.users.model', TestUser::class);
    }

    protected function defineDatabaseMigrations()
    {
        // 1. Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        // 2. Load your package migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /** @test */
    public function it_applies_discounts_correctly_with_stacking_and_usage_cap()
    {
        Event::fake([DiscountApplied::class]);

        // Create test user
        $user = TestUser::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $discount1 = Discount::create([
            'name' => 'First Discount',
            'code' => 'FIRST20',
            'percentage' => 20.00,
            'user_limit' => 1,
            'is_active' => true,
        ]);

        $discount2 = Discount::create([
            'name' => 'Second Discount',
            'code' => 'SECOND10',
            'percentage' => 10.00,
            'user_limit' => 1,
            'is_active' => true,
        ]);

        UserDiscount::assign($user, $discount1);
        UserDiscount::assign($user, $discount2);

        $result = UserDiscount::apply($user, 100.00);

        $this->assertEquals(28.00, $result['total_discount']);
        $this->assertCount(2, $result['applied']);
        $this->assertEquals('FIRST20', $result['applied'][0]['discount']->code);
        $this->assertEquals(20.00, $result['applied'][0]['amount']);
        $this->assertEquals(8.00, $result['applied'][1]['amount']);

        // Usage cap
        $this->assertEquals(1, $user->fresh()->userDiscounts()->where('discount_id', $discount1->id)->first()->usage_count);

        $secondResult = UserDiscount::apply($user, 100.00);
        $this->assertEquals(0.0, $secondResult['total_discount']);

        // Audits
        $this->assertEquals(2, DiscountAudit::where('action', 'apply')->count());

        // Events
        Event::assertDispatched(DiscountApplied::class, 2);
    }
}

// Minimal User model for testing
class TestUser extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];

    public function userDiscounts()
    {
        return $this->hasMany(\Acme\UserDiscounts\Models\UserDiscount::class);
    }
}