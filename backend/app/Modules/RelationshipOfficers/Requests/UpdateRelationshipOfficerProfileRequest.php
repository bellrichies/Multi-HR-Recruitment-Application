<?php

declare(strict_types=1);

namespace App\Modules\RelationshipOfficers\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateRelationshipOfficerProfileRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'employee_code' => 'required|string|max:80',
        ]);
    }
}
