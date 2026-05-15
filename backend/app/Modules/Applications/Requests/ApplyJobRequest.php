<?php

declare(strict_types=1);

namespace App\Modules\Applications\Requests;

use App\Core\Request;
use App\Core\Validator;

class ApplyJobRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'cover_letter' => 'nullable|string|max:5000',
        ]);
    }
}
