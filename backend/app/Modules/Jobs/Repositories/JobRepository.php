<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Repositories;

use App\Core\Database;
use PDO;

class JobRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO jobs
             (uuid, recruiter_id, created_by, title, slug, description, requirements, responsibilities, location,
              employment_type, work_mode, salary_min, salary_max, currency, experience_level, application_deadline,
              assigned_hr_officer_id, assigned_relationship_officer_id,
              status, created_at, updated_at)
             VALUES
             (:uuid, :recruiter_id, :created_by, :title, :slug, :description, :requirements, :responsibilities, :location,
              :employment_type, :work_mode, :salary_min, :salary_max, :currency, :experience_level, :application_deadline,
              :assigned_hr_officer_id, :assigned_relationship_officer_id,
              "draft", NOW(), NOW())'
        );
        $statement->execute([
            'uuid' => $data['uuid'],
            'recruiter_id' => $data['recruiter_id'],
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?? null,
            'responsibilities' => $data['responsibilities'] ?? null,
            'location' => $data['location'],
            'employment_type' => $data['employment_type'],
            'work_mode' => $data['work_mode'],
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'currency' => $data['currency'] ?? 'NGN',
            'experience_level' => $data['experience_level'] ?? null,
            'application_deadline' => $data['application_deadline'] ?? null,
            'assigned_hr_officer_id' => $data['assigned_hr_officer_id'] ?? null,
            'assigned_relationship_officer_id' => $data['assigned_relationship_officer_id'] ?? null,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function update(int $id, array $data): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE jobs SET
                title = :title,
                description = :description,
                requirements = :requirements,
                responsibilities = :responsibilities,
                location = :location,
                employment_type = :employment_type,
                work_mode = :work_mode,
                salary_min = :salary_min,
                salary_max = :salary_max,
                currency = :currency,
                experience_level = :experience_level,
                application_deadline = :application_deadline,
                updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL'
        );
        $statement->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'],
            'requirements' => $data['requirements'] ?? null,
            'responsibilities' => $data['responsibilities'] ?? null,
            'location' => $data['location'],
            'employment_type' => $data['employment_type'],
            'work_mode' => $data['work_mode'],
            'salary_min' => $data['salary_min'] ?? null,
            'salary_max' => $data['salary_max'] ?? null,
            'currency' => $data['currency'] ?? 'NGN',
            'experience_level' => $data['experience_level'] ?? null,
            'application_deadline' => $data['application_deadline'] ?? null,
        ]);

        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM jobs WHERE id = :id AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['id' => $id]);
        $job = $statement->fetch();

        return is_array($job) ? $job : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM jobs WHERE slug = :slug AND deleted_at IS NULL LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $job = $statement->fetch();

        return is_array($job) ? $job : null;
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), (int) config('jobs.max_per_page', 100));
        $where = ['deleted_at IS NULL'];
        $params = [];

        foreach (['status', 'recruiter_id', 'assigned_hr_officer_id', 'assigned_relationship_officer_id'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        if (! empty($filters['public'])) {
            $publicStatuses = config('jobs.public_statuses', ['published', 'open', 'assigned']);
            $statusPlaceholders = [];

            foreach ($publicStatuses as $index => $status) {
                $key = 'public_status_' . $index;
                $statusPlaceholders[] = ':' . $key;
                $params[$key] = $status;
            }

            $where[] = 'status IN (' . implode(', ', $statusPlaceholders) . ')';
        }

        if (! empty($filters['search'])) {
            $where[] = '(title LIKE :search OR location LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM jobs WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);
        $sql = "SELECT * FROM jobs WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $statement = $this->connection()->prepare($sql);
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

    public function updateStatus(int $id, string $status): array
    {
        $publishedAt = in_array($status, ['published', 'open'], true) ? 'NOW()' : 'published_at';
        $closedAt = in_array($status, ['closed', 'cancelled', 'filled'], true) ? 'NOW()' : 'closed_at';
        $statement = $this->connection()->prepare(
            "UPDATE jobs SET status = :status, published_at = {$publishedAt}, closed_at = {$closedAt}, updated_at = NOW()
             WHERE id = :id AND deleted_at IS NULL"
        );
        $statement->execute(['id' => $id, 'status' => $status]);

        return $this->findById($id);
    }

    public function assign(int $id, string $type, int $userId): array
    {
        $column = $type === 'hr_officer' ? 'assigned_hr_officer_id' : 'assigned_relationship_officer_id';
        $statement = $this->connection()->prepare("UPDATE jobs SET {$column} = :user_id, status = 'assigned', updated_at = NOW() WHERE id = :id");
        $statement->execute(['id' => $id, 'user_id' => $userId]);

        return $this->findById($id);
    }

    public function slugExists(string $slug): bool
    {
        $statement = $this->connection()->prepare('SELECT id FROM jobs WHERE slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug]);

        return (bool) $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
