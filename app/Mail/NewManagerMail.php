<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewManagerMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $plainPassword;
    public string $loginUrl;

    public function __construct(
        public User $manager,
        string $plainPassword
    ) {
        $this->plainPassword = $plainPassword;
        $this->loginUrl = config('app.url') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'بيانات دخولك إلى منصة وفرة الخليجية — Your Wafra Gulf Login Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-manager',
        );
    }
}
