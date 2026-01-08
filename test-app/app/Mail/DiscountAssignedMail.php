<?php

namespace App\Mail;

use Acme\UserDiscounts\Events\DiscountAssigned;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DiscountAssigned $event
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You’ve Received a Special Discount!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.discount-assigned',
            with: [
                'user'       => $this->event->user,
                'discount'   => $this->event->discount,
                'code'       => $this->event->discount->code,
                'percentage' => $this->event->discount->percentage,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}