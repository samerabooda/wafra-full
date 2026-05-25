<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('auth.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('استعادة كلمة المرور — وفرة الخليجية')
            ->greeting('مرحباً ' . $notifiable->name . '،')
            ->line('لقد تلقينا طلباً لاستعادة كلمة المرور الخاصة بحسابك.')
            ->action('استعادة كلمة المرور', $url)
            ->line('هذا الرابط صالح لمدة **60 دقيقة** فقط.')
            ->line('إذا لم تطلب استعادة كلمة المرور، يمكنك تجاهل هذا البريد بأمان.')
            ->salutation('مع تحيات فريق وفرة الخليجية للخدمات المالية');
    }
}
