<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Resources;

class JobResource
{
    public static function make(?array $job, array $skills = [], array $assignments = [], array $statusLogs = []): ?array
    {
        if ($job === null) {
            return null;
        }

        return [
            'id' => (int) $job['id'],
            'uuid' => $job['uuid'],
            'recruiter_id' => (int) $job['recruiter_id'],
            'created_by' => (int) $job['created_by'],
            'assigned_hr_officer_id' => $job['assigned_hr_officer_id'] === null ? null : (int) $job['assigned_hr_officer_id'],
            'assigned_relationship_officer_id' => $job['assigned_relationship_officer_id'] === null ? null : (int) $job['assigned_relationship_officer_id'],
            'title' => $job['title'],
            'slug' => $job['slug'],
            'description' => $job['description'],
            'requirements' => $job['requirements'],
            'responsibilities' => $job['responsibilities'],
            'location' => $job['location'],
            'employment_type' => $job['employment_type'],
            'work_mode' => $job['work_mode'],
            'salary_min' => $job['salary_min'] === null ? null : (float) $job['salary_min'],
            'salary_max' => $job['salary_max'] === null ? null : (float) $job['salary_max'],
            'currency' => $job['currency'],
            'experience_level' => $job['experience_level'],
            'application_deadline' => $job['application_deadline'],
            'status' => $job['status'],
            'published_at' => $job['published_at'],
            'closed_at' => $job['closed_at'],
            'skills' => array_map(fn (array $skill): array => [
                'id' => (int) $skill['id'],
                'skill_name' => $skill['skill_name'],
                'required_level' => $skill['required_level'],
            ], $skills),
            'assignments' => array_map(fn (array $assignment): array => [
                'id' => (int) $assignment['id'],
                'assigned_to_user_id' => (int) $assignment['assigned_to_user_id'],
                'assigned_by_user_id' => (int) $assignment['assigned_by_user_id'],
                'assignment_type' => $assignment['assignment_type'],
                'status' => $assignment['status'],
                'created_at' => $assignment['created_at'],
            ], $assignments),
            'status_logs' => array_map(fn (array $log): array => [
                'id' => (int) $log['id'],
                'from_status' => $log['from_status'],
                'to_status' => $log['to_status'],
                'changed_by' => (int) $log['changed_by'],
                'action' => $log['action'],
                'note' => $log['note'],
                'created_at' => $log['created_at'],
            ], $statusLogs),
            'created_at' => $job['created_at'],
            'updated_at' => $job['updated_at'],
        ];
    }

    public static function collection(array $jobs): array
    {
        return array_map(fn (array $job): array => self::make($job), $jobs);
    }
}
