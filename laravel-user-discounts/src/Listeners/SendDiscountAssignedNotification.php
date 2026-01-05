<?php

namespace Acme\UserDiscounts\Listeners;

use Acme\UserDiscounts\Events\DiscountAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendDiscountAssignedNotification implements ShouldQueue
{
    public function handle(DiscountAssigned $event): void
    {
        // Example: Send email, push notification, log to external service, etc.
        // \Log::info("Discount {$event->discount->code} assigned to user {$event->user->id}");

        // You could dispatch a notification:
        // $event->user->notify(new \App\Notifications\DiscountAssigned($event->discount));
    }
}