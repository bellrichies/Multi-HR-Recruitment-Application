<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Requests;

use App\Core\Request;
use App\Core\Validator;

class AssignAssessmentRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'job_seeker_id' => 'required|integer',
            'job_id' => 'nullable|integer',
            'application_id' => 'nullable|integer',
            'due_date' => 'nullable|date',
        ]);
    }
}
