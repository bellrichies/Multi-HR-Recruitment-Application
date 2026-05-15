<?php

declare(strict_types=1);

namespace App\Modules\HR\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateHrOfficerProfileRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'employee_code' => 'required|string|max:80',
            'referral_code' => 'nullable|string|max:80',
        ]);
    }
}
