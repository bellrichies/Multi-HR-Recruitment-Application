<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Resources;

class AssessmentResource
{
    public static function make(array $assessment, array $questions = []): array
    {
        return [
            'id' => (int) $assessment['id'],
            'title' => $assessment['title'],
            'description' => $assessment['description'],
            'assessment_type' => $assessment['assessment_type'],
            'duration_minutes' => (int) $assessment['duration_minutes'],
            'pass_mark' => (float) $assessment['pass_mark'],
            'status' => $assessment['status'],
            'created_by' => (int) $assessment['created_by'],
            'questions' => AssessmentQuestionResource::collection($questions),
            'created_at' => $assessment['created_at'],
            'updated_at' => $assessment['updated_at'],
        ];
    }

    public static function collection(array $assessments): array
    {
        return array_map(fn (array $assessment): array => self::make($assessment), $assessments);
    }
}
