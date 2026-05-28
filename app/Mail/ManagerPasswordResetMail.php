<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManagerPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $newPassword;
    public string $loginUrl;

    public function __construct(
        public User $manager,
        string $newPassword
    ) {
        $this->newPassword = $newPassword;
        $this->loginUrl = config('app.url') . '/login';
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تمت إعادة تعيين كلمة مرورك — Your Password Has Been Reset',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manager-password-reset',
        );
    }
}
