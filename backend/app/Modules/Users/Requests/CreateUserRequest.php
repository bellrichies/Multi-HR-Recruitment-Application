<?php

declare(strict_types=1);

namespace App\Modules\Users\Requests;

use App\Core\Request;
use App\Core\ValidationException;
use App\Core\Validator;

class CreateUserRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|max:255',
            'status' => 'nullable|string|in:pending,active,suspended,deactivated,rejected',
        ]);

        $roleIds = $request->input('role_ids', []);

        if ($roleIds !== [] && ! is_array($roleIds)) {
            throw new ValidationException(['role_ids' => ['Role ids must be an array.']]);
        }

        foreach ($roleIds as $roleId) {
            if (filter_var($roleId, FILTER_VALIDATE_INT) === false) {
                throw new ValidationException(['role_ids' => ['Every role id must be an integer.']]);
            }
        }

        $data['role_ids'] = array_values(array_unique(array_map('intval', $roleIds)));

        return $data;
    }
}
