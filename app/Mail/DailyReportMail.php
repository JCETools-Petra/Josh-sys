<?php

namespace App\Mail;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $property;
    public $reportData;

    public function __construct(Property $property, array $reportData)
    {
        $this->property = $property;
        $this->reportData = $reportData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Daily Report - ' . $this->property->name . ' - ' . now()->format('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-report',
            with: [
                'property' => $this->property,
                'data' => $this->reportData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
