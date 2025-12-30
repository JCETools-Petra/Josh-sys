<?php

namespace App\Mail;

use App\Models\RoomStay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckInConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $roomStay;
    public $property;

    /**
     * Create a new message instance.
     */
    public function __construct(RoomStay $roomStay)
    {
        $this->roomStay = $roomStay->load(['guest', 'hotelRoom.roomType', 'property']);
        $this->property = $roomStay->property;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Check-in Confirmation - ' . $this->property->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkin-confirmation',
            with: [
                'roomStay' => $this->roomStay,
                'property' => $this->property,
                'guest' => $this->roomStay->guest,
                'room' => $this->roomStay->hotelRoom,
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
