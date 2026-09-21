<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public int $daysLeft
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysLeft > 0
            ? "Your Sasampa plan ends in {$this->daysLeft} day" . ($this->daysLeft > 1 ? 's' : '')
            : 'Your Sasampa plan has ended';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.billing.reminder',
            with: [
                'company' => $this->company,
                'daysLeft' => $this->daysLeft,
                'billingUrl' => route('billing.index'),
            ],
        );
    }
}
