<?php

declare(strict_types=1);

namespace App\Modules\Auth\Repositories;

use App\Core\Database;
use PDO;

class TokenRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function blacklist(string $jti, int $userId, string $expiresAt): void
    {
        $statement = $this->connection()->prepare(
            'INSERT IGNORE INTO auth_token_blacklist (jti, user_id, expires_at, created_at)
             VALUES (:jti, :user_id, :expires_at, NOW())'
        );
        $statement->execute([
            'jti' => $jti,
            'user_id' => $userId,
            'expires_at' => $expiresAt,
        ]);
    }

    public function isBlacklisted(string $jti): bool
    {
        $statement = $this->connection()->prepare(
            'SELECT id FROM auth_token_blacklist WHERE jti = :jti AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute(['jti' => $jti]);

        return (bool) $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
