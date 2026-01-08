<?php

namespace Acme\UserDiscounts\Listeners;

use Acme\UserDiscounts\Events\DiscountRevoked;
use Acme\UserDiscounts\Mail\DiscountRevokedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDiscountRevokedNotification implements ShouldQueue
{
    public function handle(DiscountRevoked $event): void
    {
        Mail::to('pandeynarendra.18080107033@gmail.com')->send(new DiscountRevokedMail($event));
    }
}