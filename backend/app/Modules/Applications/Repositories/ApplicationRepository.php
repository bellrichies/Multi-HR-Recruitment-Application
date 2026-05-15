<?php

declare(strict_types=1);

namespace App\Modules\Applications\Repositories;

use App\Core\Database;
use PDO;

class ApplicationRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO job_applications
             (job_id, job_seeker_id, applied_by, status, current_stage, cover_letter, match_score, submitted_at, created_at, updated_at)
             VALUES (:job_id, :job_seeker_id, :applied_by, "active", "applied", :cover_letter, :match_score, NOW(), NOW(), NOW())'
        );
        $statement->execute([
            'job_id' => $data['job_id'],
            'job_seeker_id' => $data['job_seeker_id'],
            'applied_by' => $data['applied_by'],
            'cover_letter' => $data['cover_letter'] ?? null,
            'match_score' => $data['match_score'] ?? null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findExisting(int $jobId, int $profileId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM job_applications WHERE job_id = :job_id AND job_seeker_id = :profile_id LIMIT 1'
        );
        $statement->execute(['job_id' => $jobId, 'profile_id' => $profileId]);
        $application = $statement->fetch();

        return is_array($application) ? $application : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM job_applications WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $application = $statement->fetch();

        return is_array($application) ? $application : null;
    }

    public function list(array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['1 = 1'];
        $params = [];

        foreach (['job_id', 'job_seeker_id', 'current_stage'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM job_applications WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT * FROM job_applications WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}"
        );
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }

    public function updateStage(int $id, string $stage, string $status = 'active'): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE job_applications SET current_stage = :stage, status = :status, updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'stage' => $stage, 'status' => $status]);

        return $this->findById($id);
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
