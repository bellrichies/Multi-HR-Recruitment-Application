<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Repositories;

use App\Core\Database;
use PDO;

class InterviewRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO interviews
             (job_id, application_id, job_seeker_id, recruiter_id, scheduled_by, interview_type, meeting_link, scheduled_at, duration_minutes, status, created_at, updated_at)
             VALUES (:job_id, :application_id, :job_seeker_id, :recruiter_id, :scheduled_by, :interview_type, :meeting_link, :scheduled_at, :duration_minutes, "scheduled", NOW(), NOW())'
        );
        $statement->execute([
            'job_id' => $data['job_id'],
            'application_id' => $data['application_id'],
            'job_seeker_id' => $data['job_seeker_id'],
            'recruiter_id' => $data['recruiter_id'],
            'scheduled_by' => $data['scheduled_by'],
            'interview_type' => $data['interview_type'],
            'meeting_link' => $data['meeting_link'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM interviews WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $interview = $statement->fetch();

        return is_array($interview) ? $interview : null;
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['1 = 1'];
        $params = [];

        foreach (['job_id', 'application_id', 'job_seeker_id', 'recruiter_id', 'status'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM interviews WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare("SELECT * FROM interviews WHERE {$whereSql} ORDER BY scheduled_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    public function reschedule(int $id, array $data): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE interviews SET interview_type = :interview_type, meeting_link = :meeting_link,
                scheduled_at = :scheduled_at, duration_minutes = :duration_minutes, status = "rescheduled", updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'interview_type' => $data['interview_type'],
            'meeting_link' => $data['meeting_link'] ?? null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $data['duration_minutes'] ?? 60,
        ]);

        return $this->findById($id);
    }

    public function updateStatus(int $id, string $status): array
    {
        $statement = $this->connection()->prepare('UPDATE interviews SET status = :status, updated_at = NOW() WHERE id = :id');
        $statement->execute(['id' => $id, 'status' => $status]);

        return $this->findById($id);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
