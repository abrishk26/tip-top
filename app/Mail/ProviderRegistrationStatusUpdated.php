<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderRegistrationStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public string $status;
    public string $providerName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $providerName, string $status)
    {
        $this->providerName = $providerName;
        $this->status = $status;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->status === 'accepted'
            ? 'Your registration was accepted'
            : 'Your registration was rejected';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.provider_status_updated',
            with: [
                'status' => $this->status,
                'providerName' => $this->providerName,
            ],
        );
    }
}
