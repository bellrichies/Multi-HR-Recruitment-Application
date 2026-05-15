<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Repositories;

use App\Core\Database;
use PDO;

class ConversationRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO conversations (conversation_type, subject, job_id, created_by, created_at, updated_at)
             VALUES (:conversation_type, :subject, :job_id, :created_by, NOW(), NOW())'
        );
        $statement->execute([
            'conversation_type' => $data['conversation_type'],
            'subject' => $data['subject'] ?? null,
            'job_id' => $data['job_id'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function addParticipant(int $conversationId, int $userId): void
    {
        $statement = $this->connection()->prepare(
            'INSERT IGNORE INTO conversation_participants (conversation_id, user_id, created_at)
             VALUES (:conversation_id, :user_id, NOW())'
        );
        $statement->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM conversations WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $conversation = $statement->fetch();

        return is_array($conversation) ? $conversation : null;
    }

    public function participants(int $conversationId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT users.id, users.first_name, users.last_name, users.email
             FROM conversation_participants
             INNER JOIN users ON users.id = conversation_participants.user_id
             WHERE conversation_participants.conversation_id = :conversation_id
             ORDER BY conversation_participants.created_at ASC'
        );
        $statement->execute(['conversation_id' => $conversationId]);

        return $statement->fetchAll();
    }

    public function userIsParticipant(int $conversationId, int $userId): bool
    {
        $statement = $this->connection()->prepare(
            'SELECT id FROM conversation_participants WHERE conversation_id = :conversation_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);

        return (bool) $statement->fetch();
    }

    public function listForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $count = $this->connection()->prepare(
            'SELECT COUNT(*) FROM conversations
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = conversations.id
             WHERE conversation_participants.user_id = :user_id'
        );
        $count->execute(['user_id' => $userId]);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT conversations.* FROM conversations
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = conversations.id
             WHERE conversation_participants.user_id = :user_id
             ORDER BY conversations.updated_at DESC, conversations.id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute(['user_id' => $userId]);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    public function touch(int $conversationId): void
    {
        $this->connection()->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id')->execute(['id' => $conversationId]);
    }

    public function markRead(int $conversationId, int $userId): void
    {
        $this->connection()->prepare(
            'UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = :conversation_id AND user_id = :user_id'
        )->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);
        $this->connection()->prepare(
            'UPDATE messages SET read_at = COALESCE(read_at, NOW()) WHERE conversation_id = :conversation_id AND sender_id <> :user_id'
        )->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
