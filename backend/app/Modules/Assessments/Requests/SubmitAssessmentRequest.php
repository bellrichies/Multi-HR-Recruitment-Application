<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Requests;

use App\Core\HttpException;
use App\Core\Request;

class SubmitAssessmentRequest
{
    public function validate(Request $request): array
    {
        $answers = $request->input('answers');

        if (! is_array($answers) || $answers === []) {
            throw new HttpException('Assessment answers are required.', 422, [
                'answers' => ['Provide at least one answer.'],
            ]);
        }

        return ['answers' => $answers];
    }
}
