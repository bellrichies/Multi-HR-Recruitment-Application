<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Repositories;

use App\Core\Database;
use PDO;

class AssessmentRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO assessments (title, description, assessment_type, duration_minutes, pass_mark, created_by, status, created_at, updated_at)
             VALUES (:title, :description, :assessment_type, :duration_minutes, :pass_mark, :created_by, :status, NOW(), NOW())'
        );
        $statement->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assessment_type' => $data['assessment_type'] ?? 'general',
            'duration_minutes' => $data['duration_minutes'],
            'pass_mark' => $data['pass_mark'] ?? 50,
            'created_by' => $data['created_by'],
            'status' => $data['status'] ?? 'draft',
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function update(int $id, array $data): array
    {
        $statement = $this->connection()->prepare(
            'UPDATE assessments SET title = :title, description = :description, assessment_type = :assessment_type,
                duration_minutes = :duration_minutes, pass_mark = :pass_mark, status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assessment_type' => $data['assessment_type'] ?? 'general',
            'duration_minutes' => $data['duration_minutes'],
            'pass_mark' => $data['pass_mark'] ?? 50,
            'status' => $data['status'] ?? 'draft',
        ]);

        return $this->findById($id);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessments WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $assessment = $statement->fetch();

        return is_array($assessment) ? $assessment : null;
    }

    public function list(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['1 = 1'];
        $params = [];

        foreach (['status', 'assessment_type', 'created_by'] as $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $where[] = "{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM assessments WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare("SELECT * FROM assessments WHERE {$whereSql} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $statement->execute($params);

        return [
            'data' => $statement->fetchAll(),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int) max(1, ceil($total / $perPage))],
        ];
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
