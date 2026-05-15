<?php

declare(strict_types=1);

namespace App\Modules\RelationshipOfficers\Repositories;

use App\Core\Database;
use PDO;

class RelationshipOfficerProfileRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM relationship_officer_profiles WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function upsert(int $userId, array $data): array
    {
        $existing = $this->findByUserId($userId);
        $payload = [
            'user_id' => $userId,
            'employee_code' => $data['employee_code'] ?? null,
        ];

        if ($existing === null) {
            $statement = $this->connection()->prepare(
                'INSERT INTO relationship_officer_profiles (user_id, employee_code, created_at, updated_at)
                 VALUES (:user_id, :employee_code, NOW(), NOW())'
            );
        } else {
            $statement = $this->connection()->prepare(
                'UPDATE relationship_officer_profiles SET employee_code = :employee_code, updated_at = NOW()
                 WHERE user_id = :user_id'
            );
        }

        $statement->execute($payload);

        return $this->findByUserId($userId);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
