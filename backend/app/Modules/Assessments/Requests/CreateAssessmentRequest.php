<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Requests;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Validator;

class CreateAssessmentRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'title' => 'required|string|max:160',
            'description' => 'nullable|string',
            'assessment_type' => 'nullable|string|max:80',
            'duration_minutes' => 'required|integer',
            'pass_mark' => 'required|numeric',
            'status' => 'nullable|string|in:draft,active,archived',
        ]);

        if ((int) $data['duration_minutes'] <= 0 || (float) $data['pass_mark'] < 0 || (float) $data['pass_mark'] > 100) {
            throw new HttpException('Assessment duration and pass mark are invalid.', 422, [
                'duration_minutes' => ['Duration must be greater than zero.'],
                'pass_mark' => ['Pass mark must be between 0 and 100.'],
            ]);
        }

        return $data;
    }
}
