<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Repositories;

use App\Core\Database;
use PDO;

class InterviewFeedbackRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(array $data): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO interview_feedback (interview_id, submitted_by, rating, feedback, recommendation, created_at, updated_at)
             VALUES (:interview_id, :submitted_by, :rating, :feedback, :recommendation, NOW(), NOW())'
        );
        $statement->execute([
            'interview_id' => $data['interview_id'],
            'submitted_by' => $data['submitted_by'],
            'rating' => $data['rating'] ?? null,
            'feedback' => $data['feedback'],
            'recommendation' => $data['recommendation'],
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function forInterview(int $interviewId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM interview_feedback WHERE interview_id = :id ORDER BY created_at DESC');
        $statement->execute(['id' => $interviewId]);

        return $statement->fetchAll();
    }

    private function findById(int $id): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM interview_feedback WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
