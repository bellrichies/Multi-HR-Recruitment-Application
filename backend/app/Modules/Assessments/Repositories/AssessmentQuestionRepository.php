<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Repositories;

use App\Core\Database;
use PDO;

class AssessmentQuestionRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO assessment_questions
             (assessment_id, question_text, question_type, options_json, correct_answer_json, score, created_at, updated_at)
             VALUES (:assessment_id, :question_text, :question_type, :options_json, :correct_answer_json, :score, NOW(), NOW())'
        );
        $statement->execute([
            'assessment_id' => $data['assessment_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'options_json' => isset($data['options']) ? json_encode($data['options'], JSON_THROW_ON_ERROR) : null,
            'correct_answer_json' => isset($data['correct_answer']) ? json_encode($data['correct_answer'], JSON_THROW_ON_ERROR) : null,
            'score' => $data['score'] ?? 1,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessment_questions WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $question = $statement->fetch();

        return is_array($question) ? $question : null;
    }

    public function forAssessment(int $assessmentId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM assessment_questions WHERE assessment_id = :id ORDER BY id ASC');
        $statement->execute(['id' => $assessmentId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
