<?php

declare(strict_types=1);

namespace App\Modules\Reports\Resources;

class ActivityLogResource
{
    public static function make(array $log): array
    {
        return [
            'id' => (int) $log['id'],
            'user' => [
                'id' => $log['user_id'] === null ? null : (int) $log['user_id'],
                'name' => $log['user_name'] ?? null,
                'email' => $log['user_email'] ?? null,
            ],
            'activity_type' => $log['activity_type'],
            'description' => $log['description'],
            'metadata' => self::decode($log['metadata_json'] ?? null),
            'created_at' => $log['created_at'],
        ];
    }

    public static function collection(array $logs): array
    {
        return array_map(static fn (array $log): array => self::make($log), $logs);
    }

    private static function decode(?string $json): mixed
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
