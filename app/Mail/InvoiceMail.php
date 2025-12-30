<?php

namespace App\Mail;

use App\Models\RoomStay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $roomStay;
    public $property;

    /**
     * Create a new message instance.
     */
    public function __construct(RoomStay $roomStay)
    {
        $this->roomStay = $roomStay->load([
            'guest',
            'hotelRoom.roomType',
            'property',
            'fnbOrders.items.menuItem',
            'payments',
            'refunds',
        ]);
        $this->property = $roomStay->property;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invoice - ' . $this->property->name . ' - ' . $this->roomStay->confirmation_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'roomStay' => $this->roomStay,
                'property' => $this->property,
                'guest' => $this->roomStay->guest,
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
        // Auto-generate and attach invoice PDF
        try {
            $pdf = \PDF::loadView('frontoffice.invoice-pdf', [
                'roomStay' => $this->roomStay,
            ]);

            $filename = 'invoice-' . $this->roomStay->confirmation_number . '.pdf';

            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), $filename)
                    ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to generate invoice PDF attachment', [
                'room_stay_id' => $this->roomStay->id,
                'error' => $e->getMessage(),
            ]);

            // Return empty array if PDF generation fails
            return [];
        }
    }
}
