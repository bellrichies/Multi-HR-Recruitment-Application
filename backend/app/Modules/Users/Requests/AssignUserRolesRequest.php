<?php

declare(strict_types=1);

namespace App\Modules\Users\Requests;

use App\Core\Request;
use App\Core\ValidationException;

class AssignUserRolesRequest
{
    public function validate(Request $request): array
    {
        $roleIds = $request->input('role_ids');

        if (! is_array($roleIds) || $roleIds === []) {
            throw new ValidationException(['role_ids' => ['At least one role id is required.']]);
        }

        foreach ($roleIds as $roleId) {
            if (filter_var($roleId, FILTER_VALIDATE_INT) === false) {
                throw new ValidationException(['role_ids' => ['Every role id must be an integer.']]);
            }
        }

        return ['role_ids' => array_map('intval', $roleIds)];
    }
}
