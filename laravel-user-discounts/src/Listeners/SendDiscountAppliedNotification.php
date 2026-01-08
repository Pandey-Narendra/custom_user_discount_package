<?php

namespace Acme\UserDiscounts\Listeners;

use Acme\UserDiscounts\Events\DiscountApplied;
use Acme\UserDiscounts\Mail\DiscountAppliedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountAppliedNotification implements ShouldQueue
{
    public function handle(DiscountApplied $event): void
    {
        Mail::to('pandeynarendra.18080107033@gmail.com')->send(new DiscountAppliedMail($event));
    }
}