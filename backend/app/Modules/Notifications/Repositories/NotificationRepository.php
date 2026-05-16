<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Repositories;

use App\Core\Database;
use PDO;

class NotificationRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO notifications (user_id, title, body, type, channel, data_json, created_at, updated_at)
             VALUES (:user_id, :title, :body, :type, :channel, :data_json, NOW(), NOW())'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'],
            'channel' => $data['channel'] ?? 'in_app',
            'data_json' => isset($data['data']) ? json_encode($data['data'], JSON_THROW_ON_ERROR) : null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM notifications WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $notification = $statement->fetch();

        return is_array($notification) ? $notification : null;
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM notifications WHERE id = :id AND user_id = :user_id LIMIT 1');
        $statement->execute(['id' => $id, 'user_id' => $userId]);
        $notification = $statement->fetch();

        return is_array($notification) ? $notification : null;
    }

    public function listForUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $count = $this->connection()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id');
        $count->execute(['user_id' => $userId]);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute(['user_id' => $userId]);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    public function markRead(int $id, int $userId): ?array
    {
        $statement = $this->connection()->prepare(
            'UPDATE notifications SET read_at = COALESCE(read_at, NOW()), updated_at = NOW() WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $this->findForUser($id, $userId);
    }

    public function markUnread(int $id, int $userId): ?array
    {
        $statement = $this->connection()->prepare(
            'UPDATE notifications SET read_at = NULL, updated_at = NOW() WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $this->findForUser($id, $userId);
    }

    public function markAllRead(int $userId): void
    {
        $this->connection()->prepare(
            'UPDATE notifications SET read_at = COALESCE(read_at, NOW()), updated_at = NOW() WHERE user_id = :user_id'
        )->execute(['user_id' => $userId]);
    }

    public function unreadCount(int $userId): int
    {
        $statement = $this->connection()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND read_at IS NULL');
        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
