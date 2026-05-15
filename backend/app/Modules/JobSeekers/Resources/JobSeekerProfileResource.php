<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Resources;

class JobSeekerProfileResource
{
    public static function make(?array $profile, array $details = []): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'id' => (int) $profile['id'],
            'user_id' => (int) $profile['user_id'],
            'profile_code' => $profile['profile_code'],
            'gender' => $profile['gender'],
            'date_of_birth' => $profile['date_of_birth'],
            'location' => $profile['location'],
            'current_job_title' => $profile['current_job_title'],
            'years_of_experience' => $profile['years_of_experience'] === null ? null : (float) $profile['years_of_experience'],
            'salary_expectation_min' => $profile['salary_expectation_min'] === null ? null : (float) $profile['salary_expectation_min'],
            'salary_expectation_max' => $profile['salary_expectation_max'] === null ? null : (float) $profile['salary_expectation_max'],
            'availability_status' => $profile['availability_status'],
            'profile_completion_percentage' => (int) $profile['profile_completion_percentage'],
            'assigned_hr_officer_id' => $profile['assigned_hr_officer_id'] === null ? null : (int) $profile['assigned_hr_officer_id'],
            'referred_by_hr_officer_id' => $profile['referred_by_hr_officer_id'] === null ? null : (int) $profile['referred_by_hr_officer_id'],
            'onboarding_status' => $profile['onboarding_status'],
            'completed_steps' => json_decode($profile['completed_steps_json'] ?? '[]', true) ?: [],
            'skills' => $details['skills'] ?? [],
            'work_experiences' => $details['work_experiences'] ?? [],
            'educations' => $details['educations'] ?? [],
            'certifications' => $details['certifications'] ?? [],
            'documents' => self::documents($details['documents'] ?? []),
            'guarantors' => self::guarantors($details['guarantors'] ?? []),
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['updated_at'],
        ];
    }

    private static function documents(array $documents): array
    {
        return array_map(fn (array $document): array => [
            'id' => (int) $document['id'],
            'document_type' => $document['document_type'],
            'status' => $document['status'],
            'reviewed_at' => $document['reviewed_at'] ?? null,
            'created_at' => $document['created_at'],
        ], $documents);
    }

    private static function guarantors(array $guarantors): array
    {
        return array_map(function (array $guarantor): array {
            unset($guarantor['document_path']);

            return $guarantor;
        }, $guarantors);
    }
}
