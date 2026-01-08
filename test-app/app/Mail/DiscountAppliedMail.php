<?php

namespace App\Mail;

use Acme\UserDiscounts\Events\DiscountApplied;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountAppliedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DiscountApplied $event
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Discount Has Been Applied!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.discount-applied',
            with: [
                'user'     => $this->event->user,
                'discount' => $this->event->discount,
                'amount'   => $this->event->amount,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}