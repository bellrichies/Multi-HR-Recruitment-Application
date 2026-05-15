<?php

declare(strict_types=1);

namespace App\Modules\Roles\Requests;

use App\Core\Request;
use App\Core\Validator;

class CreateRoleRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);
    }
}
