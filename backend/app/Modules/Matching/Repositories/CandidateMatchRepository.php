<?php

declare(strict_types=1);

namespace App\Modules\Matching\Repositories;

use App\Core\Database;
use PDO;

class CandidateMatchRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function upsert(int $jobId, int $profileId, ?int $matchedBy, float $score, string $reason, string $status = 'recommended'): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO candidate_matches (job_id, job_seeker_id, matched_by, match_score, match_reason, status, created_at, updated_at)
             VALUES (:job_id, :profile_id, :matched_by, :score, :reason, :status, NOW(), NOW())
             ON DUPLICATE KEY UPDATE matched_by = VALUES(matched_by), match_score = VALUES(match_score),
                match_reason = VALUES(match_reason), status = VALUES(status), updated_at = NOW()'
        );
        $statement->execute([
            'job_id' => $jobId,
            'profile_id' => $profileId,
            'matched_by' => $matchedBy,
            'score' => $score,
            'reason' => $reason,
            'status' => $status,
        ]);

        return $this->find($jobId, $profileId);
    }

    public function find(int $jobId, int $profileId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM candidate_matches WHERE job_id = :job_id AND job_seeker_id = :profile_id LIMIT 1'
        );
        $statement->execute(['job_id' => $jobId, 'profile_id' => $profileId]);
        $match = $statement->fetch();

        return is_array($match) ? $match : null;
    }

    public function forJob(int $jobId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM candidate_matches WHERE job_id = :job_id ORDER BY match_score DESC');
        $statement->execute(['job_id' => $jobId]);

        return $statement->fetchAll();
    }

    public function forCandidate(int $profileId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM candidate_matches WHERE job_seeker_id = :profile_id ORDER BY match_score DESC');
        $statement->execute(['profile_id' => $profileId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
