<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Repositories;

use App\Core\Database;
use PDO;

class JobSeekerProfileRepository
{
    public function __construct(private readonly ?PDO $db = null)
    {
    }

    public function findByUserId(int $userId): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM job_seeker_profiles WHERE user_id = :user_id LIMIT 1');
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection()->prepare('SELECT * FROM job_seeker_profiles WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $profile = $statement->fetch();

        return is_array($profile) ? $profile : null;
    }

    public function upsert(int $userId, array $data): array
    {
        $existing = $this->findByUserId($userId);
        $payload = [
            'user_id' => $userId,
            'profile_code' => $existing['profile_code'] ?? 'JS-' . strtoupper(bin2hex(random_bytes(4))),
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'location' => $data['location'] ?? null,
            'current_job_title' => $data['current_job_title'] ?? null,
            'years_of_experience' => $data['years_of_experience'] ?? null,
            'salary_expectation_min' => $data['salary_expectation_min'] ?? null,
            'salary_expectation_max' => $data['salary_expectation_max'] ?? null,
            'availability_status' => $data['availability_status'] ?? null,
            'assigned_hr_officer_id' => $data['assigned_hr_officer_id'] ?? null,
            'referred_by_hr_officer_id' => $data['referred_by_hr_officer_id'] ?? null,
        ];

        if ($existing === null) {
            $statement = $this->connection()->prepare(
                'INSERT INTO job_seeker_profiles
                 (user_id, profile_code, gender, date_of_birth, location, current_job_title, years_of_experience,
                  salary_expectation_min, salary_expectation_max, availability_status, assigned_hr_officer_id,
                  referred_by_hr_officer_id, onboarding_status, completed_steps_json, created_at, updated_at)
                 VALUES
                 (:user_id, :profile_code, :gender, :date_of_birth, :location, :current_job_title, :years_of_experience,
                  :salary_expectation_min, :salary_expectation_max, :availability_status, :assigned_hr_officer_id,
                  :referred_by_hr_officer_id, "in_progress", JSON_ARRAY("profile"), NOW(), NOW())'
            );
            $statement->execute($payload);
        } else {
            $statement = $this->connection()->prepare(
                'UPDATE job_seeker_profiles SET
                    gender = :gender,
                    date_of_birth = :date_of_birth,
                    location = :location,
                    current_job_title = :current_job_title,
                    years_of_experience = :years_of_experience,
                    salary_expectation_min = :salary_expectation_min,
                    salary_expectation_max = :salary_expectation_max,
                    availability_status = :availability_status,
                    assigned_hr_officer_id = COALESCE(:assigned_hr_officer_id, assigned_hr_officer_id),
                    referred_by_hr_officer_id = COALESCE(:referred_by_hr_officer_id, referred_by_hr_officer_id),
                    onboarding_status = IF(onboarding_status = "completed", "completed", "in_progress"),
                    updated_at = NOW()
                 WHERE user_id = :user_id'
            );
            unset($payload['profile_code']);
            $statement->execute($payload);
        }

        return $this->findByUserId($userId);
    }

    public function updateCompletion(int $profileId, int $percentage, array $completedSteps): void
    {
        $status = $percentage >= 100 ? 'completed' : 'in_progress';
        $statement = $this->connection()->prepare(
            'UPDATE job_seeker_profiles
             SET profile_completion_percentage = :percentage, onboarding_status = :status, completed_steps_json = :steps, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $profileId,
            'percentage' => $percentage,
            'status' => $status,
            'steps' => json_encode(array_values($completedSteps), JSON_THROW_ON_ERROR),
        ]);
    }

    public function counts(int $profileId): array
    {
        $tables = [
            'skills' => 'job_seeker_skills',
            'work_experiences' => 'job_seeker_work_experiences',
            'educations' => 'job_seeker_educations',
            'certifications' => 'job_seeker_certifications',
            'documents' => 'job_seeker_documents',
            'guarantors' => 'guarantors',
        ];
        $counts = [];

        foreach ($tables as $key => $table) {
            $statement = $this->connection()->prepare("SELECT COUNT(*) FROM {$table} WHERE job_seeker_id = :id");
            $statement->execute(['id' => $profileId]);
            $counts[$key] = (int) $statement->fetchColumn();
        }

        return $counts;
    }

    private function connection(): PDO
    {
        return $this->db ?? Database::connection();
    }
}
