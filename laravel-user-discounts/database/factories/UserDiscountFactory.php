<?php

namespace Acme\UserDiscounts\Database\Factories;

use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\UserDiscount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserDiscountFactory extends Factory
{
    protected $model = UserDiscount::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'discount_id'  => Discount::factory(),
            'assigned_at'  => now()->subDays($this->faker->numberBetween(1, 30)),
            'revoked_at'   => null,
            'usage_count'  => 0,
        ];
    }

    /**
     * Indicate that the discount has been revoked
     */
    public function revoked(): self
    {
        return $this->state([
            'revoked_at' => now()->subDays($this->faker->numberBetween(1, 10)),
        ]);
    }

    /**
     * Set a specific usage count (e.g., partially or fully used)
     */
    public function withUsage(int $count): self
    {
        return $this->state(['usage_count' => $count]);
    }

    /**
     * Make it fully used up (based on the related discount's user_limit)
     */
    public function usedUp(): self
    {
        return $this->afterCreating(function (UserDiscount $userDiscount) {
            $limit = $userDiscount->discount->user_limit ?? 1;
            $userDiscount->update(['usage_count' => $limit]);
        });
    }

    /**
     * Assign to an existing user (useful in tests)
     */
    public function forUser(User $user): self
    {
        return $this->state(['user_id' => $user->id]);
    }
}