<?php

declare(strict_types=1);

namespace App\Modules\HR\Repositories;

use App\Core\Database;
use PDO;

class HrOfficerProfileRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM hr_officer_profiles WHERE user_id = :user_id LIMIT 1');
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
            'referral_code' => $data['referral_code'] ?? $existing['referral_code'] ?? 'HR-' . strtoupper(bin2hex(random_bytes(3))),
        ];

        if ($existing === null) {
            $statement = $this->connection()->prepare(
                'INSERT INTO hr_officer_profiles (user_id, employee_code, referral_code, created_at, updated_at)
                 VALUES (:user_id, :employee_code, :referral_code, NOW(), NOW())'
            );
        } else {
            $statement = $this->connection()->prepare(
                'UPDATE hr_officer_profiles SET employee_code = :employee_code, referral_code = :referral_code, updated_at = NOW()
                 WHERE user_id = :user_id'
            );
        }

        $statement->execute($payload);

        return $this->findByUserId($userId);
    }

    public function findByReferralCode(string $code): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM hr_officer_profiles WHERE referral_code = :code LIMIT 1');
        $statement->execute(['code' => $code]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
