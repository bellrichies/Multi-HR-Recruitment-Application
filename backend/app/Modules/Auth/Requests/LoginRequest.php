<?php

declare(strict_types=1);

namespace App\Modules\Auth\Requests;

use App\Core\Request;
use App\Core\Validator;

class LoginRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);
    }
}
