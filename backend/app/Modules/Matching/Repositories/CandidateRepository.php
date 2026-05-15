<?php

declare(strict_types=1);

namespace App\Modules\Matching\Repositories;

use App\Core\Database;
use PDO;

class CandidateRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function discover(array $filters, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);
        $where = ['p.profile_completion_percentage > 0'];
        $params = [];

        foreach (['location', 'availability_status'] as $field) {
            if (! empty($filters[$field])) {
                $where[] = "p.{$field} = :{$field}";
                $params[$field] = $filters[$field];
            }
        }

        if (! empty($filters['salary_min'])) {
            $where[] = '(p.salary_expectation_min IS NULL OR p.salary_expectation_min >= :salary_min)';
            $params['salary_min'] = $filters['salary_min'];
        }

        if (! empty($filters['salary_max'])) {
            $where[] = '(p.salary_expectation_max IS NULL OR p.salary_expectation_max <= :salary_max)';
            $params['salary_max'] = $filters['salary_max'];
        }

        if (! empty($filters['experience_min'])) {
            $where[] = 'p.years_of_experience >= :experience_min';
            $params['experience_min'] = $filters['experience_min'];
        }

        if (! empty($filters['skill'])) {
            $where[] = 'EXISTS (SELECT 1 FROM job_seeker_skills s WHERE s.job_seeker_id = p.id AND s.skill_name LIKE :skill)';
            $params['skill'] = '%' . $filters['skill'] . '%';
        }

        if (! empty($filters['education'])) {
            $where[] = 'EXISTS (SELECT 1 FROM job_seeker_educations e WHERE e.job_seeker_id = p.id AND (e.qualification LIKE :education OR e.field_of_study LIKE :education))';
            $params['education'] = '%' . $filters['education'] . '%';
        }

        $whereSql = implode(' AND ', $where);
        $count = $this->connection()->prepare("SELECT COUNT(*) FROM job_seeker_profiles p WHERE {$whereSql}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $offset = ($page - 1) * $perPage;
        $statement = $this->connection()->prepare(
            "SELECT p.*, u.first_name, u.last_name
             FROM job_seeker_profiles p
             INNER JOIN users u ON u.id = p.user_id
             WHERE {$whereSql}
             ORDER BY p.profile_completion_percentage DESC, p.updated_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
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

    public function findProfile(int $profileId): ?array
    {
        $statement = $this->connection()->prepare(
            'SELECT p.*, u.first_name, u.last_name, u.email, u.phone
             FROM job_seeker_profiles p
             INNER JOIN users u ON u.id = p.user_id
             WHERE p.id = :id LIMIT 1'
        );
        $statement->execute(['id' => $profileId]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function skills(int $profileId): array
    {
        $statement = $this->connection()->prepare('SELECT skill_name FROM job_seeker_skills WHERE job_seeker_id = :id');
        $statement->execute(['id' => $profileId]);

        return array_column($statement->fetchAll(), 'skill_name');
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
