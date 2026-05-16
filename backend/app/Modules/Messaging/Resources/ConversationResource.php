<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Resources;

class ConversationResource
{
    public static function make(array $conversation, array $participants = [], ?int $unreadCount = null): array
    {
        return [
            'id' => (int) $conversation['id'],
            'conversation_type' => $conversation['conversation_type'],
            'subject' => $conversation['subject'],
            'job_id' => $conversation['job_id'] === null ? null : (int) $conversation['job_id'],
            'created_by' => (int) $conversation['created_by'],
            'participants' => array_map(fn (array $participant): array => [
                'id' => (int) $participant['id'],
                'name' => trim($participant['first_name'] . ' ' . $participant['last_name']),
                'email' => $participant['email'],
            ], $participants),
            'unread_count' => $unreadCount ?? (isset($conversation['unread_count']) ? (int) $conversation['unread_count'] : null),
            'is_favorite' => isset($conversation['is_favorite']) && (int) $conversation['is_favorite'] === 1,
            'created_at' => $conversation['created_at'],
            'updated_at' => $conversation['updated_at'],
        ];
    }

    public static function collection(array $conversations): array
    {
        return array_map(fn (array $conversation): array => self::make($conversation, $conversation['participants'] ?? []), $conversations);
    }
}
