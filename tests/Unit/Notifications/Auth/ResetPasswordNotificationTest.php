<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Auth;

use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ResetPasswordNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_notification_generates_frontend_url(): void
    {
        config([
            'app.frontend_url' => 'http://ui-base.test:5173',
        ]);

        $user = User::factory()->create([
            'email' => 'peeyush@example.com',
        ]);

        $token = 'test-reset-token';

        $notification = new ResetPasswordNotification($token);

        $mail = $notification->toMail($user);

        $this->assertSame(
            'Reset your password',
            $mail->subject,
        );

        $this->assertStringContainsString(
            'http://ui-base.test:5173/reset-password?token=test-reset-token',
            $mail->actionUrl,
        );

        $this->assertStringContainsString(
            'email=peeyush%40example.com',
            $mail->actionUrl,
        );
    }
}
