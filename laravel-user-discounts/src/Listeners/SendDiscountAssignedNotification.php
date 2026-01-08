<?php

namespace Acme\UserDiscounts\Listeners;

use Acme\UserDiscounts\Events\DiscountAssigned;
use Acme\UserDiscounts\Mail\DiscountAssignedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountAssignedNotification implements ShouldQueue
{
    public function handle(DiscountAssigned $event): void
    {
        Mail::to('pandeynarendra.18080107033@gmail.com')->send(new DiscountAssignedMail($event));
    }
}