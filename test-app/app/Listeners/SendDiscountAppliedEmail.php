<?php

namespace App\Listeners;

use Acme\UserDiscounts\Events\DiscountApplied;
use App\Mail\DiscountAppliedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountAppliedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];

    public function handle(DiscountApplied $event): void
    {
        if (!$event->user->email) {
            return;
        }

        Mail::to($event->user->email)
            ->queue(new DiscountAppliedMail($event));
    }
}