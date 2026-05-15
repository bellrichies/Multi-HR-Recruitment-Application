<?php

declare(strict_types=1);

namespace App\Modules\Matching\Requests;

use App\Core\Request;
use App\Core\Validator;

class MatchCandidateRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'job_seeker_id' => 'nullable|integer',
        ]);
    }
}
