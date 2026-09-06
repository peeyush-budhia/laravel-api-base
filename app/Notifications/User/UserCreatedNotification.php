<?php

declare(strict_types=1);

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(
        private readonly string $activationToken,
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
            ->subject(__('users.account_created_subject'))
            ->greeting(__('users.account_created_greeting', [
                'name' => $notifiable->full_name,
            ]))
            ->line(__('users.account_created_line'))
            ->line(__('users.account_created_email', [
                'email' => $notifiable->email,
            ]))
            ->line(__('users.account_created_activation'))
            ->action(
                __('users.account_created_action'),
                $this->activationUrl($notifiable),
            )
            ->line(__('users.account_created_expiry', [
                'count' => config('auth.passwords.users.expire', 60),
            ]));
    }

    /**
     * Build the frontend account activation URL.
     */
    private function activationUrl(object $notifiable): string
    {
        return rtrim(
            (string) config('app.frontend_url'),
            '/',
        ).'/activate-account?'.http_build_query([
            'token' => $this->activationToken,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
