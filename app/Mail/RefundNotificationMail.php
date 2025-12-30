<?php

namespace App\Mail;

use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $refund;
    public $property;

    /**
     * Create a new message instance.
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund->load(['roomStay.guest', 'roomStay.hotelRoom', 'property']);
        $this->property = $refund->property;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Notification - ' . $this->property->name . ' - ' . $this->refund->refund_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.refund-notification',
            with: [
                'refund' => $this->refund,
                'property' => $this->property,
                'guest' => $this->refund->roomStay->guest,
                'roomStay' => $this->refund->roomStay,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
