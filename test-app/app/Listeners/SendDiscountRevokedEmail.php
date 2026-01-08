<?php

namespace App\Listeners;

use Acme\UserDiscounts\Events\DiscountRevoked;
use App\Mail\DiscountRevokedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountRevokedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 5;
    public $backoff = [10, 30, 60, 120, 300];

    public function handle(DiscountRevoked $event): void
    {
        if (!$event->user->email) {
            return;
        }

        Mail::to($event->user->email)
            ->queue(new DiscountRevokedMail($event));
    }
}