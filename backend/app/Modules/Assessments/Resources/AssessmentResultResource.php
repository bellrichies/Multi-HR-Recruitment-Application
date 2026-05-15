<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Resources;

class AssessmentResultResource
{
    public static function make(array $result): array
    {
        return [
            'id' => (int) $result['id'],
            'assignment_id' => (int) $result['assignment_id'],
            'total_score' => (float) $result['total_score'],
            'percentage' => (float) $result['percentage'],
            'status' => $result['status'],
            'graded_by' => $result['graded_by'] === null ? null : (int) $result['graded_by'],
            'graded_at' => $result['graded_at'],
        ];
    }
}
