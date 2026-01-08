<?php

namespace Acme\UserDiscounts\Commands;

use Acme\UserDiscounts\Models\Discount;
use Illuminate\Console\Command;

class DiscountsListCommand extends Command
{
    protected $signature = 'discounts:list {--active : Only show active discounts}';

    protected $description = 'List all discounts in the system';

    public function handle(): int
    {
        $query = Discount::query();

        if ($this->option('active')) {
            $query->active();
        }

        $discounts = $query->get();

        if ($discounts->isEmpty()) {
            $this->info('No discounts found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Code', 'Percentage', 'User Limit', 'Active', 'Starts At', 'Ends At'],
            $discounts->map(fn($d) => [
                $d->id,
                $d->name,
                $d->code,
                $d->percentage . '%',
                $d->user_limit,
                $d->is_active ? 'Yes' : 'No',
                $d->starts_at?->format('Y-m-d'),
                $d->ends_at?->format('Y-m-d'),
            ])
        );

        return self::SUCCESS;
    }
}