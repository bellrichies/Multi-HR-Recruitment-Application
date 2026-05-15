<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Repositories;

use App\Core\Database;
use PDO;

class EmailQueueRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function enqueue(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO email_queue
             (user_id, notification_id, recipient_email, subject, body, status, available_at, created_at, updated_at)
             VALUES (:user_id, :notification_id, :recipient_email, :subject, :body, "pending", NOW(), NOW(), NOW())'
        );
        $statement->execute([
            'user_id' => $data['user_id'],
            'notification_id' => $data['notification_id'] ?? null,
            'recipient_email' => $data['recipient_email'],
            'subject' => $data['subject'],
            'body' => $data['body'],
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function pending(int $limit = 25): array
    {
        $limit = min(max(1, $limit), 100);
        $statement = $this->connection()->query(
            "SELECT * FROM email_queue WHERE status IN ('pending', 'failed') AND (available_at IS NULL OR available_at <= NOW())
             ORDER BY created_at ASC LIMIT {$limit}"
        );

        return $statement->fetchAll();
    }

    public function markSent(int $id): void
    {
        $this->connection()->prepare(
            'UPDATE email_queue SET status = "sent", sent_at = NOW(), updated_at = NOW() WHERE id = :id'
        )->execute(['id' => $id]);
    }

    public function markFailed(int $id, string $error): void
    {
        $this->connection()->prepare(
            'UPDATE email_queue
             SET status = "failed", attempts = attempts + 1, last_error = :error, available_at = DATE_ADD(NOW(), INTERVAL 5 MINUTE), updated_at = NOW()
             WHERE id = :id'
        )->execute(['id' => $id, 'error' => $error]);
    }

    private function findById(int $id): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM email_queue WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
