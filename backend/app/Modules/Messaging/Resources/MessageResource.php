<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Resources;

class MessageResource
{
    public static function make(array $message): array
    {
        return [
            'id' => (int) $message['id'],
            'conversation_id' => (int) $message['conversation_id'],
            'sender_id' => (int) $message['sender_id'],
            'message_body' => $message['message_body'],
            'attachment_path' => $message['attachment_path'],
            'read_at' => $message['read_at'],
            'created_at' => $message['created_at'],
        ];
    }

    public static function collection(array $messages): array
    {
        return array_map(fn (array $message): array => self::make($message), $messages);
    }
}
