<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\{Content, Envelope, Address};
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $resetUrl,
        public readonly string $userName,
        public readonly int    $expiresMinutes = 60
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'noreply@wafragulf.com'),
                config('mail.from.name',    'وفرة الخليجية')
            ),
            subject: 'إعادة تعيين كلمة المرور — وفرة الخليجية',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'resetUrl'       => $this->resetUrl,
                'userName'       => $this->userName,
                'expiresMinutes' => $this->expiresMinutes,
            ],
        );
    }
}
