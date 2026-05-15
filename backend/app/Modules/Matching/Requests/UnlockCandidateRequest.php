<?php

declare(strict_types=1);

namespace App\Modules\Matching\Requests;

use App\Core\Request;
use App\Core\Validator;

class UnlockCandidateRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'job_id' => 'nullable|integer',
            'payment_source' => 'nullable|string|in:wallet',
        ]);
    }
}
