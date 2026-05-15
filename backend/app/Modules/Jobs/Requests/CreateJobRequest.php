<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Requests;

use App\Core\Request;
use App\Core\ValidationException;
use App\Core\Validator;

class CreateJobRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'recruiter_id' => 'nullable|integer',
            'title' => 'required|string|max:180',
            'description' => 'required|string|max:5000',
            'requirements' => 'nullable|string|max:5000',
            'responsibilities' => 'nullable|string|max:5000',
            'location' => 'required|string|max:150',
            'employment_type' => 'required|string|max:80',
            'work_mode' => 'required|string|max:80',
            'salary_min' => 'nullable|numeric',
            'salary_max' => 'nullable|numeric',
            'currency' => 'nullable|string|max:3',
            'experience_level' => 'nullable|string|max:80',
            'application_deadline' => 'nullable|date',
        ]);

        $skills = $request->input('skills', []);

        if (! is_array($skills)) {
            throw new ValidationException(['skills' => ['Skills must be an array.']]);
        }

        $data['skills'] = $skills;

        return $data;
    }
}
