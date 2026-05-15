<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Repositories;

use App\Core\Database;
use PDO;

class AssessmentAnswerRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function upsert(int $assignmentId, int $questionId, mixed $answer, ?float $scoreAwarded): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO assessment_answers (assignment_id, question_id, answer_json, score_awarded, created_at, updated_at)
             VALUES (:assignment_id, :question_id, :answer_json, :score_awarded, NOW(), NOW())
             ON DUPLICATE KEY UPDATE answer_json = VALUES(answer_json), score_awarded = VALUES(score_awarded), updated_at = NOW()'
        );
        $statement->execute([
            'assignment_id' => $assignmentId,
            'question_id' => $questionId,
            'answer_json' => json_encode($answer, JSON_THROW_ON_ERROR),
            'score_awarded' => $scoreAwarded,
        ]);

        return $this->find($assignmentId, $questionId);
    }

    public function forAssignment(int $assignmentId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessment_answers WHERE assignment_id = :id ORDER BY id ASC');
        $statement->execute(['id' => $assignmentId]);

        return $statement->fetchAll();
    }

    private function find(int $assignmentId, int $questionId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM assessment_answers WHERE assignment_id = :assignment_id AND question_id = :question_id LIMIT 1'
        );
        $statement->execute(['assignment_id' => $assignmentId, 'question_id' => $questionId]);

        return $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
