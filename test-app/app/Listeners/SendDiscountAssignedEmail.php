<?php

namespace App\Listeners;

use Acme\UserDiscounts\Events\DiscountAssigned;
use App\Mail\DiscountAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountAssignedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    // Number of retry attempts if email fails
    public $tries = 5;

    // Delay between retries (in seconds)
    public $backoff = [10, 30, 60, 120, 300]; // 10s, 30s, 1min, 2min, 5min

    public function handle(DiscountAssigned $event): void
    {
        if (!$event->user->email) {
            return;
        }

        Mail::to($event->user->email)
            ->queue(new DiscountAssignedMail($event)); 
    }
}