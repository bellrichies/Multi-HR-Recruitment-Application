<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Requests;

use App\Core\Request;
use App\Core\Validator;

class ReviewRecruiterRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'reason' => 'nullable|string|max:1000',
        ]);
    }
}
