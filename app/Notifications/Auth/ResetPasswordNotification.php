<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth.reset_password_subject'))
            ->greeting(__('auth.reset_password_greeting'))
            ->line(__('auth.reset_password_line'))
            ->action(
                __('auth.reset_password_action'),
                $this->resetUrl($notifiable),
            )
            ->line(
                __('auth.reset_password_expiry', [
                    'count' => config(
                        'auth.passwords.users.expire',
                        60,
                    ),
                ]),
            )
            ->line(__('auth.reset_password_ignore'));
    }

    /**
     * Build the frontend password reset URL.
     */
    private function resetUrl(object $notifiable): string
    {
        return rtrim(
            (string) config('app.frontend_url'),
            '/',
        ).'/reset-password?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
