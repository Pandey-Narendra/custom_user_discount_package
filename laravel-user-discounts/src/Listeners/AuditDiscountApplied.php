<?php

namespace Acme\UserDiscounts\Listeners;

use Illuminate\Support\Facades\DB;
use Acme\UserDiscounts\Models\DiscountAudit;
use Acme\UserDiscounts\Events\DiscountAssigned;

class AuditDiscountApplied
{
    public function handle(DiscountAssigned $event): void
    {
        DB::transaction(function () use ($event) {
            DiscountAudit::create([
                'user_id' => $event->userDiscount->user_id,
                'discount_id' => $event->userDiscount->discount_id,
                'action' => 'applied',
                'meta' => ['uses_remaining' => $event->userDiscount->uses_remaining],
                'amount_discounted' => $event->amount,
            ]);
        });
    }
}