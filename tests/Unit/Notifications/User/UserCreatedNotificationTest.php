<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\User;

use App\Notifications\User\UserCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\TestCase;

final class UserCreatedNotificationTest extends TestCase
{
    public function test_user_created_notification_is_queued(): void
    {
        $notification = new UserCreatedNotification('temporary-password');

        $this->assertInstanceOf(ShouldQueue::class, $notification);
    }
}
