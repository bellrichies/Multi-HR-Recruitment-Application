<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Repositories;

use App\Core\Database;
use PDO;

class JobSkillRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function sync(int $jobId, array $skills): void
    {
        $db = $this->connection();
        $db->prepare('DELETE FROM job_skills WHERE job_id = :job_id')->execute(['job_id' => $jobId]);
        $statement = $db->prepare(
            'INSERT INTO job_skills (job_id, skill_name, required_level, created_at)
             VALUES (:job_id, :skill_name, :required_level, NOW())'
        );

        foreach ($skills as $skill) {
            $statement->execute([
                'job_id' => $jobId,
                'skill_name' => is_array($skill) ? $skill['skill_name'] : (string) $skill,
                'required_level' => is_array($skill) ? ($skill['required_level'] ?? null) : null,
            ]);
        }
    }

    public function forJob(int $jobId): array
    {
        $statement = $this->connection()->prepare('SELECT * FROM job_skills WHERE job_id = :job_id ORDER BY skill_name');
        $statement->execute(['job_id' => $jobId]);

        return $statement->fetchAll();
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
