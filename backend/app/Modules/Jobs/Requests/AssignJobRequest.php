<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Requests;

use App\Core\Request;
use App\Core\Validator;

class AssignJobRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'user_id' => 'required|integer',
        ]);
    }
}
