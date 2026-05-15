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

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
