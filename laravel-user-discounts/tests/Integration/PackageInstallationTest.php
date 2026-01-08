<?php

namespace Acme\UserDiscounts\Tests\Integration;

use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Facades\UserDiscount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class PackageInstallationTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [\Acme\UserDiscounts\UserDiscountServiceProvider::class];
    }

    public function test_installation_and_migrations(): void
    {
        // Run migrations (Testbench handles in-memory DB)
        $this->artisan('migrate')->assertExitCode(0);

        // Verify tables
        $this->assertTrue(\Schema::hasTable('discounts'));
        $this->assertTrue(\Schema::hasTable('user_discounts'));
        $this->assertTrue(\Schema::hasTable('discount_audits'));

        // Test assign/apply workflow
        $user = \App\Models\User::factory()->create();
        $discount = Discount::factory()->create(['percentage' => 10.0, 'user_limit' => 1]);

        UserDiscount::assign($user, $discount);

        $result = UserDiscount::apply($user, 100.0);

        $this->assertEquals(10.0, $result['total_discount']);
        $this->assertCount(1, $result['applied']);
    }
}