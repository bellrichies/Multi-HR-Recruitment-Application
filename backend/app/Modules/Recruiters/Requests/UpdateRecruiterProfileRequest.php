<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateRecruiterProfileRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'company_name' => 'required|string|max:180',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:30',
            'company_website' => 'nullable|string|max:255',
            'industry' => 'required|string|max:120',
            'company_size' => 'required|string|max:80',
            'rc_number' => 'nullable|string|max:100',
            'address' => 'required|string|max:1000',
        ]);
    }
}
