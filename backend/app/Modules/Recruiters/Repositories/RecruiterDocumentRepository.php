<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Repositories;

use App\Core\Database;
use PDO;

class RecruiterDocumentRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(int $profileId, string $documentType, string $filePath): array
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO recruiter_documents (recruiter_id, document_type, file_path, status, created_at, updated_at)
             VALUES (:recruiter_id, :document_type, :file_path, "pending", NOW(), NOW())'
        );
        $statement->execute([
            'recruiter_id' => $profileId,
            'document_type' => $documentType,
            'file_path' => $filePath,
        ]);

        return $this->findById((int) $this->connection()->lastInsertId());
    }

    public function forProfile(int $profileId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM recruiter_documents WHERE recruiter_id = :id ORDER BY created_at DESC');
        $statement->execute(['id' => $profileId]);

        return $statement->fetchAll();
    }

    private function findById(int $id): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM recruiter_documents WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        return $statement->fetch();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
