<?php

namespace App\Support;

use App\Models\SystemNotification;

class NotificationHelper
{
    public static function send(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        SystemNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }
}
