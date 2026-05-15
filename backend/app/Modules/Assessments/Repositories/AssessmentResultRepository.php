<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Repositories;

use App\Core\Database;
use PDO;

class AssessmentResultRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function upsert(int $assignmentId, float $totalScore, float $percentage, string $status, ?int $gradedBy): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO assessment_results (assignment_id, total_score, percentage, status, graded_by, graded_at, created_at, updated_at)
             VALUES (:assignment_id, :total_score, :percentage, :status, :graded_by, IF(:graded_by IS NULL, NULL, NOW()), NOW(), NOW())
             ON DUPLICATE KEY UPDATE total_score = VALUES(total_score), percentage = VALUES(percentage),
                status = VALUES(status), graded_by = VALUES(graded_by), graded_at = VALUES(graded_at), updated_at = NOW()'
        );
        $statement->execute([
            'assignment_id' => $assignmentId,
            'total_score' => $totalScore,
            'percentage' => $percentage,
            'status' => $status,
            'graded_by' => $gradedBy,
        ]);

        return $this->findByAssignment($assignmentId);
    }

    public function findByAssignment(int $assignmentId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessment_results WHERE assignment_id = :id LIMIT 1');
        $statement->execute(['id' => $assignmentId]);
        $result = $statement->fetch();

        return is_array($result) ? $result : null;
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
