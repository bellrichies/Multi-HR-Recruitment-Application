<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Repositories;

use App\Core\Database;
use PDO;

class AssessmentAssignmentRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO assessment_assignments
             (assessment_id, job_seeker_id, job_id, application_id, assigned_by, status, due_date, created_at, updated_at)
             VALUES (:assessment_id, :job_seeker_id, :job_id, :application_id, :assigned_by, "assigned", :due_date, NOW(), NOW())'
        );
        $statement->execute([
            'assessment_id' => $data['assessment_id'],
            'job_seeker_id' => $data['job_seeker_id'],
            'job_id' => $data['job_id'] ?? null,
            'application_id' => $data['application_id'] ?? null,
            'assigned_by' => $data['assigned_by'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessment_assignments WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $assignment = $statement->fetch();

        return is_array($assignment) ? $assignment : null;
    }

    public function findExisting(int $assessmentId, int $profileId, ?int $jobId, ?int $applicationId): ?array
    {
        $where = 'assessment_id = :assessment_id AND job_seeker_id = :job_seeker_id';
        $params = ['assessment_id' => $assessmentId, 'job_seeker_id' => $profileId];

        if ($applicationId !== null) {
            $where .= ' AND application_id = :application_id';
            $params['application_id'] = $applicationId;
        } elseif ($jobId !== null) {
            $where .= ' AND job_id = :job_id AND application_id IS NULL';
            $params['job_id'] = $jobId;
        } else {
            $where .= ' AND job_id IS NULL AND application_id IS NULL';
        }

        $statement = $this->connection()->prepare("SELECT * FROM assessment_assignments WHERE {$where} LIMIT 1");
        $statement->execute($params);
        $assignment = $statement->fetch();

        return is_array($assignment) ? $assignment : null;
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['1 = 1'];
        $params = [];

        foreach (['assessment_id', 'job_seeker_id', 'job_id', 'application_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $where[] = "assessment_assignments.{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        $join = '';

        if (isset($filters['recruiter_id']) && $filters['recruiter_id'] !== '') {
            $join = ' INNER JOIN jobs ON jobs.id = assessment_assignments.job_id';
            $where[] = 'jobs.recruiter_id = :recruiter_id';
            $params['recruiter_id'] = $filters['recruiter_id'];
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM assessment_assignments{$join} WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT assessment_assignments.* FROM assessment_assignments{$join} WHERE {$whereSql} ORDER BY assessment_assignments.created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    public function start(int $id): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE assessment_assignments SET status = "in_progress", started_at = COALESCE(started_at, NOW()), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id]);

        return $this->findById($id);
    }

    public function submit(int $id, string $status = 'submitted'): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE assessment_assignments SET status = :status, submitted_at = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'status' => $status]);

        return $this->findById($id);
    }

    public function updateStatus(int $id, string $status): array
    {
        $statement = $this->connection()->prepare('UPDATE assessment_assignments SET status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id, 'status' => $status]);

        return $this->findById($id);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
