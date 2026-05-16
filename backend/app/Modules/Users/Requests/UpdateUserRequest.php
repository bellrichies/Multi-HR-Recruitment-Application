<?php

declare(strict_types=1);

namespace App\Modules\Users\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateUserRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8|max:255',
        ]);
    }
}
