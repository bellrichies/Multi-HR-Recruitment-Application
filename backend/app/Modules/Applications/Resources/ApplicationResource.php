<?php

declare(strict_types=1);

namespace App\Modules\Applications\Resources;

class ApplicationResource
{
    public static function make(?array $application, array $logs = []): ?array
    {
        if ($application === null) {
            return null;
        }

        return [
            'id' => (int) $application['id'],
            'job_id' => (int) $application['job_id'],
            'job_seeker_id' => (int) $application['job_seeker_id'],
            'applied_by' => (int) $application['applied_by'],
            'status' => $application['status'],
            'current_stage' => $application['current_stage'],
            'cover_letter' => $application['cover_letter'],
            'match_score' => $application['match_score'] === null ? null : (float) $application['match_score'],
            'submitted_at' => $application['submitted_at'],
            'stage_logs' => array_map(fn (array $log): array => [
                'id' => (int) $log['id'],
                'from_stage' => $log['from_stage'],
                'to_stage' => $log['to_stage'],
                'changed_by' => (int) $log['changed_by'],
                'note' => $log['note'],
                'created_at' => $log['created_at'],
            ], $logs),
            'created_at' => $application['created_at'],
            'updated_at' => $application['updated_at'],
        ];
    }

    public static function collection(array $applications): array
    {
        return array_map(fn (array $application): array => self::make($application), $applications);
    }
}
