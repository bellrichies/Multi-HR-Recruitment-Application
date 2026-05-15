<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Repositories;

use App\Core\Database;
use PDO;

class NotificationPreferenceRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function getOrCreate(int $userId): array
    {
        $existing = $this->findByUser($userId);

        if ($existing !== null) {
            return $existing;
        }

        $this->connection()->prepare(
            'INSERT INTO notification_preferences (user_id, in_app_enabled, email_enabled, created_at, updated_at)
             VALUES (:user_id, 1, 1, NOW(), NOW())'
        )->execute(['user_id' => $userId]);

        return $this->findByUser($userId);
    }

    public function update(int $userId, bool $inAppEnabled, bool $emailEnabled, array $eventPreferences): array
    {
        $this->getOrCreate($userId);
        $statement = $this->connection()->prepare(
            'UPDATE notification_preferences
             SET in_app_enabled = :in_app_enabled, email_enabled = :email_enabled, event_preferences_json = :event_preferences_json, updated_at = NOW()
             WHERE user_id = :user_id'
        );
        $statement->execute([
            'user_id' => $userId,
            'in_app_enabled' => $inAppEnabled ? 1 : 0,
            'email_enabled' => $emailEnabled ? 1 : 0,
            'event_preferences_json' => json_encode($eventPreferences, JSON_THROW_ON_ERROR),
        ]);

        return $this->findByUser($userId);
    }

    private function findByUser(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM notification_preferences WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $preference = $statement->fetch();

        return is_array($preference) ? $preference : null;
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
