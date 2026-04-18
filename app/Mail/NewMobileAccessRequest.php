<?php

namespace App\Mail;

use App\Models\MobileAppRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMobileAccessRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public MobileAppRequest $mobileAppRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Mobile Access Request: ' . $this->mobileAppRequest->company->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.admin.new-mobile-access-request',
            with: [
                'mobileAppRequest' => $this->mobileAppRequest,
                'company' => $this->mobileAppRequest->company,
                'reviewUrl' => route('admin.mobile-access.show', $this->mobileAppRequest),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
