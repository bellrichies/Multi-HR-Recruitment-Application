<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Requests;

use App\Core\HttpException;
use App\Core\Request;

class GradeAssessmentRequest
{
    public function validate(Request $request): array
    {
        $scores = $request->input('scores');

        if (! is_array($scores) || $scores === []) {
            throw new HttpException('Manual grading scores are required.', 422, [
                'scores' => ['Provide question_id to score mappings.'],
            ]);
        }

        return ['scores' => $scores];
    }
}
