<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Resources;

class AssessmentAssignmentResource
{
    public static function make(array $assignment, ?array $result = null, array $answers = []): array
    {
        return [
            'id' => (int) $assignment['id'],
            'assessment_id' => (int) $assignment['assessment_id'],
            'job_seeker_id' => (int) $assignment['job_seeker_id'],
            'job_id' => $assignment['job_id'] === null ? null : (int) $assignment['job_id'],
            'application_id' => $assignment['application_id'] === null ? null : (int) $assignment['application_id'],
            'assigned_by' => (int) $assignment['assigned_by'],
            'status' => $assignment['status'],
            'due_date' => $assignment['due_date'],
            'started_at' => $assignment['started_at'],
            'submitted_at' => $assignment['submitted_at'],
            'result' => $result === null ? null : AssessmentResultResource::make($result),
            'answers' => AssessmentAnswerResource::collection($answers),
            'created_at' => $assignment['created_at'],
            'updated_at' => $assignment['updated_at'],
        ];
    }

    public static function collection(array $assignments): array
    {
        return array_map(fn (array $assignment): array => self::make($assignment), $assignments);
    }
}
