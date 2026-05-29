<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginUrl;

    public function __construct(
        public string $email,
        public ?string $branchName,
        public string $role
    ) {
        $this->loginUrl = config('app.url') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'دعوة للتسجيل في منصة وفرة الخليجية — Wafra Gulf Registration Invitation',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manager-invite',
        );
    }
}
