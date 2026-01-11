<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $invitationUrl
    ) {}

    public function envelope(): Envelope
    {
        $companyName = $this->user->company?->name ?? config('app.name');

        return new Envelope(
            subject: "You've been invited to join {$companyName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user.invitation',
            with: [
                'user' => $this->user,
                'invitationUrl' => $this->invitationUrl,
                'companyName' => $this->user->company?->name ?? config('app.name'),
                'inviterName' => auth()->user()?->name ?? 'Your administrator',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
