<?php

declare(strict_types=1);

namespace App\Modules\Matching\Resources;

class CandidateResource
{
    public static function summary(array $profile, ?array $match = null): array
    {
        return [
            'id' => (int) $profile['id'],
            'profile_code' => $profile['profile_code'],
            'name' => trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')),
            'location' => $profile['location'],
            'current_job_title' => $profile['current_job_title'],
            'years_of_experience' => $profile['years_of_experience'] === null ? null : (float) $profile['years_of_experience'],
            'availability_status' => $profile['availability_status'],
            'profile_completion_percentage' => (int) $profile['profile_completion_percentage'],
            'match_score' => $match['match_score'] ?? null,
            'match_reason' => $match['match_reason'] ?? null,
        ];
    }

    public static function full(array $profile, array $skills = [], ?array $match = null): array
    {
        return array_merge(self::summary($profile, $match), [
            'email' => $profile['email'] ?? null,
            'phone' => $profile['phone'] ?? null,
            'gender' => $profile['gender'],
            'date_of_birth' => $profile['date_of_birth'],
            'salary_expectation_min' => $profile['salary_expectation_min'] === null ? null : (float) $profile['salary_expectation_min'],
            'salary_expectation_max' => $profile['salary_expectation_max'] === null ? null : (float) $profile['salary_expectation_max'],
            'skills' => $skills,
        ]);
    }
}
