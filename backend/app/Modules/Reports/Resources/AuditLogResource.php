<?php

declare(strict_types=1);

namespace App\Modules\Reports\Resources;

class AuditLogResource
{
    public static function make(array $log): array
    {
        return [
            'id' => (int) $log['id'],
            'actor' => [
                'id' => $log['actor_id'] === null ? null : (int) $log['actor_id'],
                'name' => $log['actor_name'] ?? null,
                'email' => $log['actor_email'] ?? null,
            ],
            'action' => $log['action'],
            'module' => $log['module'],
            'entity_type' => $log['entity_type'],
            'entity_id' => $log['entity_id'] === null ? null : (int) $log['entity_id'],
            'old_values' => self::decode($log['old_values_json'] ?? null),
            'new_values' => self::decode($log['new_values_json'] ?? null),
            'ip_address' => $log['ip_address'],
            'user_agent' => $log['user_agent'],
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
