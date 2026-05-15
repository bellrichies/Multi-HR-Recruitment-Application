<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Repositories;

use App\Core\Database;
use PDO;

class JobStatusLogRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(int $jobId, ?string $fromStatus, string $toStatus, int $changedBy, string $action, ?string $note = null): void
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO job_status_logs (job_id, from_status, to_status, changed_by, action, note, created_at)
             VALUES (:job_id, :from_status, :to_status, :changed_by, :action, :note, NOW())'
        );
        $statement->execute([
            'job_id' => $jobId,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy,
            'action' => $action,
            'note' => $note,
        ]);
    }

    public function forJob(int $jobId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM job_status_logs WHERE job_id = :job_id ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['job_id' => $jobId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
