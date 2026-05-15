<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Repositories;

use App\Core\Database;
use PDO;

class RecruiterProfileRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM recruiter_profiles WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM recruiter_profiles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function upsert(int $userId, array $data): array
    {
        $existing = $this->findByUserId($userId);
        $payload = [
            'user_id' => $userId,
            'company_name' => $data['company_name'] ?? null,
            'company_email' => $data['company_email'] ?? null,
            'company_phone' => $data['company_phone'] ?? null,
            'company_website' => $data['company_website'] ?? null,
            'industry' => $data['industry'] ?? null,
            'company_size' => $data['company_size'] ?? null,
            'rc_number' => $data['rc_number'] ?? null,
            'address' => $data['address'] ?? null,
        ];

        if ($existing === null) {
            $statement = $this->connection()->prepare(
                'INSERT INTO recruiter_profiles
                 (user_id, company_name, company_email, company_phone, company_website, industry, company_size, rc_number, address, verification_status, created_at, updated_at)
                 VALUES
                 (:user_id, :company_name, :company_email, :company_phone, :company_website, :industry, :company_size, :rc_number, :address, "pending", NOW(), NOW())'
            );
            $statement->execute($payload);
        } else {
            $statement = $this->connection()->prepare(
                'UPDATE recruiter_profiles SET
                    company_name = :company_name,
                    company_email = :company_email,
                    company_phone = :company_phone,
                    company_website = :company_website,
                    industry = :industry,
                    company_size = :company_size,
                    rc_number = :rc_number,
                    address = :address,
                    verification_status = IF(verification_status = "verified", verification_status, "under_review"),
                    updated_at = NOW()
                 WHERE user_id = :user_id'
            );
            $statement->execute($payload);
        }

        return $this->findByUserId($userId);
    }

    public function updateVerification(int $id, string $status, int $reviewerId, ?string $reason = null): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE recruiter_profiles
             SET verification_status = :status, verified_at = :verified_at, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => $status,
            'verified_at' => $status === 'verified' ? date('Y-m-d H:i:s') : null,
        ]);

        if ($status === 'rejected') {
            $this->connection()->prepare(
                'UPDATE recruiter_documents SET status = "rejected", reviewed_by = :reviewed_by, reviewed_at = NOW(), rejection_reason = :reason
                 WHERE recruiter_id = :id AND status = "pending"'
            )->execute(['id' => $id, 'reviewed_by' => $reviewerId, 'reason' => $reason]);
        }

        return $this->findById($id);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
