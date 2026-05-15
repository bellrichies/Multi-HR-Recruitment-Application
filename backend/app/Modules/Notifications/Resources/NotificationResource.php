<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Resources;

class NotificationResource
{
    public static function make(array $notification): array
    {
        return [
            'id' => (int) $notification['id'],
            'title' => $notification['title'],
            'body' => $notification['body'],
            'type' => $notification['type'],
            'channel' => $notification['channel'],
            'data' => $notification['data_json'] === null ? null : json_decode((string) $notification['data_json'], true),
            'read_at' => $notification['read_at'],
            'created_at' => $notification['created_at'],
        ];
    }

    public static function collection(array $notifications): array
    {
        return array_map(fn (array $notification): array => self::make($notification), $notifications);
    }
}
