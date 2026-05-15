<?php

declare(strict_types=1);

namespace App\Modules\Applications\Repositories;

use App\Core\Database;
use PDO;

class ApplicationStageLogRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function create(int $applicationId, ?string $fromStage, string $toStage, int $changedBy, ?string $note = null): void
    {
        $statement = $this->connection()->prepare(
            'INSERT INTO application_stage_logs (application_id, from_stage, to_stage, changed_by, note, created_at)
             VALUES (:application_id, :from_stage, :to_stage, :changed_by, :note, NOW())'
        );
        $statement->execute([
            'application_id' => $applicationId,
            'from_stage' => $fromStage,
            'to_stage' => $toStage,
            'changed_by' => $changedBy,
            'note' => $note,
        ]);
    }

    public function forApplication(int $applicationId): array
    {
        $statement = $this->connection()->prepare(
            'SELECT * FROM application_stage_logs WHERE application_id = :id ORDER BY created_at DESC, id DESC'
        );
        $statement->execute(['id' => $applicationId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
