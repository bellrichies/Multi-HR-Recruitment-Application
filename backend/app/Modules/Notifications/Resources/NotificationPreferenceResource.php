<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Resources;

class NotificationPreferenceResource
{
    public static function make(array $preference): array
    {
        return [
            'user_id' => (int) $preference['user_id'],
            'in_app_enabled' => (bool) $preference['in_app_enabled'],
            'email_enabled' => (bool) $preference['email_enabled'],
            'event_preferences' => $preference['event_preferences_json'] === null ? [] : json_decode((string) $preference['event_preferences_json'], true),
            'updated_at' => $preference['updated_at'],
        ];
    }
}
