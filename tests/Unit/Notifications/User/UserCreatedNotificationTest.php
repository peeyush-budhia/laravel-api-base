<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\User;

use App\Models\User;
use App\Notifications\User\UserCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_created_notification_is_queued_after_commit(): void
    {
        $notification = new UserCreatedNotification('activation-token');

        $this->assertInstanceOf(
            ShouldQueueAfterCommit::class,
            $notification,
        );
    }

    public function test_notification_contains_an_expiring_activation_url(): void
    {
        config([
            'app.frontend_url' => 'http://ui-base.test:5173',
            'auth.passwords.users.expire' => 45,
        ]);

        $user = User::factory()->create([
            'email' => 'new-user@example.com',
        ]);
        $notification = new UserCreatedNotification('activation-token');
        $mail = $notification->toMail($user);

        $this->assertSame('Activate Account', $mail->actionText);
        $this->assertStringContainsString(
            'http://ui-base.test:5173/activate-account?token=activation-token',
            $mail->actionUrl,
        );
        $this->assertStringContainsString(
            'email=new-user%40example.com',
            $mail->actionUrl,
        );
        $content = implode(' ', [
            ...$mail->introLines,
            ...$mail->outroLines,
        ]);

        $this->assertStringContainsString(
            'This activation link expires in 45 minutes.',
            $content,
        );
        $this->assertStringNotContainsString(
            'Temporary password:',
            $content,
        );
        $this->assertStringNotContainsString(
            'password',
            serialize($notification),
        );
    }
}
