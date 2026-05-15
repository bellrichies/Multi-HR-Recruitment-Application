<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateJobSeekerProfileRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'gender' => 'nullable|string|max:30',
            'date_of_birth' => 'nullable|date',
            'location' => 'required|string|max:150',
            'current_job_title' => 'required|string|max:150',
            'years_of_experience' => 'nullable|numeric',
            'salary_expectation_min' => 'nullable|numeric',
            'salary_expectation_max' => 'nullable|numeric',
            'availability_status' => 'required|string|max:80',
            'referral_code' => 'nullable|string|max:80',
        ]);
    }
}
