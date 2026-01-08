<?php

namespace App\Mail;

use Acme\UserDiscounts\Events\DiscountRevoked;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DiscountRevokedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DiscountRevoked $event
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A Discount Has Been Removed',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.discount-revoked',
            with: [
                'user'     => $this->event->user,
                'discount' => $this->event->discount,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}