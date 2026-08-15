<?php

declare(strict_types=1);

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $temporaryPassword,
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
            ->line(__('users.account_created_password', [
                'password' => $this->temporaryPassword,
            ]))
            ->action(
                __('users.account_created_action'),
                $this->loginUrl(),
            )
            ->line(__('users.account_created_security'));
    }

    /**
     * Build the frontend sign-in URL.
     */
    private function loginUrl(): string
    {
        return rtrim(
            (string) config('app.frontend_url'),
            '/',
        ).'/signin';
    }
}
