<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Repositories;

use App\Core\Database;
use PDO;

class JobAssignmentRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(int $jobId, int $assignedTo, int $assignedBy, string $type): array
    {
        $this->connection()->prepare(
            'UPDATE job_assignments SET status = "inactive", updated_at = NOW()
             WHERE job_id = :job_id AND assignment_type = :type'
        )->execute(['job_id' => $jobId, 'type' => $type]);

        $statement = $this->connection()->prepare(
            'INSERT INTO job_assignments (job_id, assigned_to_user_id, assigned_by_user_id, assignment_type, status, created_at, updated_at)
             VALUES (:job_id, :assigned_to, :assigned_by, :type, "active", NOW(), NOW())'
        );
        $statement->execute([
            'job_id' => $jobId,
            'assigned_to' => $assignedTo,
            'assigned_by' => $assignedBy,
            'type' => $type,
        ]);

        $fetch = $this->connection()->prepare('SELECT * FROM job_assignments WHERE id = :id LIMIT 1');
        $fetch->execute(['id' => (int) $this->connection()->lastInsertId()]);

        return $fetch->fetch();
    }

    public function forJob(int $jobId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM job_assignments WHERE job_id = :job_id ORDER BY created_at DESC');
        $statement->execute(['job_id' => $jobId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
