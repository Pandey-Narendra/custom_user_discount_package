<?php

namespace Acme\UserDiscounts\Database\Factories;

use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountAuditFactory extends Factory
{
    protected $model = DiscountAudit::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'discount_id' => Discount::factory(),
            'action'      => $this->faker->randomElement(['assign', 'apply', 'revoke']),
            'old_usage'   => $this->faker->numberBetween(0, 3),
            'new_usage'   => fn (array $attributes) => $attributes['old_usage'] + 1, // apply usually +1
            'applied_at'  => now()->subMinutes($this->faker->numberBetween(1, 1440)),
            'ip_address'  => $this->faker->ipv4,
        ];
    }

    public function assign(): self
    {
        return $this->state([
            'action'     => 'assign',
            'old_usage'  => 0,
            'new_usage'  => 0,
        ]);
    }

    public function apply(): self
    {
        $old = $this->faker->numberBetween(0, 4);

        return $this->state([
            'action'     => 'apply',
            'old_usage'  => $old,
            'new_usage'  => $old + 1,
        ]);
    }

    public function revoke(): self
    {
        $usage = $this->faker->numberBetween(0, 5);

        return $this->state([
            'action'     => 'revoke',
            'old_usage'  => $usage,
            'new_usage'  => $usage, // no change on revoke
        ]);
    }
}