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

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->connection()->prepare(
            "SELECT conversations.*,
                conversation_participants.is_favorite,
                (
                    SELECT COUNT(*) FROM messages
                    WHERE messages.conversation_id = conversations.id
                        AND messages.sender_id <> :unread_user_id
                        AND (
                            conversation_participants.last_read_at IS NULL
                            OR messages.created_at > conversation_participants.last_read_at
                        )
                ) unread_count
             FROM conversations
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = conversations.id
             WHERE conversations.id = :id AND conversation_participants.user_id = :user_id
             LIMIT 1"
        );
        $statement->execute(['id' => $id, 'user_id' => $userId, 'unread_user_id' => $userId]);
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

    public function participantsForConversations(array $conversationIds): array
    {
        $conversationIds = array_values(array_unique(array_map('intval', $conversationIds)));

        if ($conversationIds === []) {
            return [];
        }

        $placeholders = [];
        $params = [];

        foreach ($conversationIds as $index => $conversationId) {
            $key = 'conversation_id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $conversationId;
        }

        $statement = $this->connection()->prepare(
            'SELECT conversation_participants.conversation_id, users.id, users.first_name, users.last_name, users.email
             FROM conversation_participants
             INNER JOIN users ON users.id = conversation_participants.user_id
             WHERE conversation_participants.conversation_id IN (' . implode(', ', $placeholders) . ')
             ORDER BY conversation_participants.created_at ASC'
        );
        $statement->execute($params);

        $participants = [];

        foreach ($statement->fetchAll() as $participant) {
            $participants[(int) $participant['conversation_id']][] = $participant;
        }

        return $participants;
    }

    public function userIsParticipant(int $conversationId, int $userId): bool
    {
        $statement = $this->connection()->prepare(
            'SELECT id FROM conversation_participants WHERE conversation_id = :conversation_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);

        return (bool) $statement->fetch();
    }

    public function listForUser(int $userId, int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['conversation_participants.user_id = :user_id'];
        $params = ['user_id' => $userId];
        $search = trim((string) ($filters['search'] ?? ''));
        $filter = (string) ($filters['filter'] ?? 'all');

        if ($search !== '') {
            $where[] = '(conversations.subject LIKE :search_subject
                OR conversations.conversation_type LIKE :search_type
                OR EXISTS (
                    SELECT 1 FROM conversation_participants participant_search
                    INNER JOIN users user_search ON user_search.id = participant_search.user_id
                    WHERE participant_search.conversation_id = conversations.id
                        AND participant_search.user_id <> :search_user_id
                        AND (
                            user_search.first_name LIKE :search_first_name
                            OR user_search.last_name LIKE :search_last_name
                            OR user_search.email LIKE :search_email
                            OR CONCAT(user_search.first_name, " ", user_search.last_name) LIKE :search_full_name
                        )
                ))';
            $likeSearch = '%' . $search . '%';
            $params['search_subject'] = $likeSearch;
            $params['search_type'] = $likeSearch;
            $params['search_user_id'] = $userId;
            $params['search_first_name'] = $likeSearch;
            $params['search_last_name'] = $likeSearch;
            $params['search_email'] = $likeSearch;
            $params['search_full_name'] = $likeSearch;
        }

        if ($filter === 'favorites') {
            $where[] = 'conversation_participants.is_favorite = 1';
        } elseif ($filter === 'unread') {
            $where[] = 'EXISTS (
                SELECT 1 FROM messages unread_messages
                WHERE unread_messages.conversation_id = conversations.id
                    AND unread_messages.sender_id <> :status_user_id
                    AND (
                        conversation_participants.last_read_at IS NULL
                        OR unread_messages.created_at > conversation_participants.last_read_at
                    )
            )';
            $params['status_user_id'] = $userId;
        } elseif ($filter === 'read') {
            $where[] = 'NOT EXISTS (
                SELECT 1 FROM messages unread_messages
                WHERE unread_messages.conversation_id = conversations.id
                    AND unread_messages.sender_id <> :status_user_id
                    AND (
                        conversation_participants.last_read_at IS NULL
                        OR unread_messages.created_at > conversation_participants.last_read_at
                    )
            )';
            $params['status_user_id'] = $userId;
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare(
            'SELECT COUNT(*) FROM conversations
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = conversations.id
             WHERE ' . $whereSql
        );
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT conversations.*,
                conversation_participants.is_favorite,
                (
                    SELECT COUNT(*) FROM messages
                    WHERE messages.conversation_id = conversations.id
                        AND messages.sender_id <> :unread_user_id
                        AND (
                            conversation_participants.last_read_at IS NULL
                            OR messages.created_at > conversation_participants.last_read_at
                        )
                ) unread_count
             FROM conversations
             INNER JOIN conversation_participants ON conversation_participants.conversation_id = conversations.id
             WHERE {$whereSql}
             ORDER BY conversations.updated_at DESC, conversations.id DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($params + ['unread_user_id' => $userId]);

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

    public function setFavorite(int $conversationId, int $userId, bool $favorite): void
    {
        $this->connection()->prepare(
            'UPDATE conversation_participants
             SET is_favorite = :is_favorite
             WHERE conversation_id = :conversation_id AND user_id = :user_id'
        )->execute([
            'conversation_id' => $conversationId,
            'user_id' => $userId,
            'is_favorite' => $favorite ? 1 : 0,
        ]);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
