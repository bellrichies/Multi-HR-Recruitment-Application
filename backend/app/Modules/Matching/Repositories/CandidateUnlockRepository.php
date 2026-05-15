<?php

declare(strict_types=1);

namespace App\Modules\Matching\Repositories;

use App\Core\Database;
use PDO;

class CandidateUnlockRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function active(int $recruiterId, int $profileId, ?int $jobId = null): ?array
    {
        $where = 'recruiter_id = :recruiter_id AND job_seeker_id = :profile_id AND (expires_at IS NULL OR expires_at > NOW())';
        $params = ['recruiter_id' => $recruiterId, 'profile_id' => $profileId];

        if ($jobId !== null) {
            $where .= ' AND (job_id = :job_id OR job_id IS NULL)';
            $params['job_id'] = $jobId;
        }

        $statement = $this->connection()->prepare("SELECT * FROM candidate_unlocks WHERE {$where} ORDER BY created_at DESC LIMIT 1");
        $statement->execute($params);
        $unlock = $statement->fetch();

        return is_array($unlock) ? $unlock : null;
    }

    public function create(int $recruiterId, int $profileId, ?int $jobId, int $transactionId, int $unlockedBy, string $expiresAt): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO candidate_unlocks (recruiter_id, job_seeker_id, job_id, transaction_id, unlocked_by, expires_at, created_at)
             VALUES (:recruiter_id, :profile_id, :job_id, :transaction_id, :unlocked_by, :expires_at, NOW())'
        );
        $statement->execute([
            'recruiter_id' => $recruiterId,
            'profile_id' => $profileId,
            'job_id' => $jobId,
            'transaction_id' => $transactionId,
            'unlocked_by' => $unlockedBy,
            'expires_at' => $expiresAt,
        ]);

        $fetch = $this->connection()->prepare('SELECT * FROM candidate_unlocks WHERE id = :id LIMIT 1');
        $fetch->execute(['id' => (int) $this->connection()->lastInsertId()]);

        return $fetch->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
