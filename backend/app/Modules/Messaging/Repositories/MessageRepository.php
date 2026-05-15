<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Repositories;

use App\Core\Database;
use PDO;

class MessageRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO messages (conversation_id, sender_id, message_body, attachment_path, created_at, updated_at)
             VALUES (:conversation_id, :sender_id, :message_body, :attachment_path, NOW(), NOW())'
        );
        $statement->execute([
            'conversation_id' => $data['conversation_id'],
            'sender_id' => $data['sender_id'],
            'message_body' => $data['message_body'],
            'attachment_path' => $data['attachment_path'] ?? null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM messages WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $message = $statement->fetch();

        return is_array($message) ? $message : null;
    }

    public function forConversation(int $conversationId, int $page = 1, int $perPage = 50): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $count = $this->connection()->prepare('SELECT COUNT(*) FROM messages WHERE conversation_id = :conversation_id');
        $count->execute(['conversation_id' => $conversationId]);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT * FROM messages WHERE conversation_id = :conversation_id ORDER BY created_at ASC, id ASC LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute(['conversation_id' => $conversationId]);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->connection()->prepare(
            'SELECT COUNT(*) FROM messages
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = messages.conversation_id
             WHERE conversation_participants.user_id = :user_id
                AND messages.sender_id <> :user_id
                AND (conversation_participants.last_read_at IS NULL OR messages.created_at > conversation_participants.last_read_at)'
        );
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
