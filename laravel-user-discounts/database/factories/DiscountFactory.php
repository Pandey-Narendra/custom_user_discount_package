<?php

namespace Acme\UserDiscounts\Database\Factories;

use Acme\UserDiscounts\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'name'       => $this->faker->words(3, true),
            'code'       => strtoupper($this->faker->unique()->lexify('??????')),
            'percentage' => $this->faker->randomFloat(2, 5, 50),
            'user_limit' => $this->faker->numberBetween(1, 5),
            'starts_at'  => now()->subDays(10),
            'ends_at'    => now()->addDays(30),
            'is_active'  => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }

    public function expired(): self
    {
        return $this->state([
            'starts_at' => now()->subDays(20),
            'ends_at'   => now()->subDays(5),
        ]);
    }
}