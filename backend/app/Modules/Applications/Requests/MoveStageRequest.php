<?php

declare(strict_types=1);

namespace App\Modules\Applications\Requests;

use App\Core\Request;
use App\Core\Validator;
use App\Modules\Applications\Services\ApplicationService;

class MoveStageRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'stage' => 'required|string|in:' . implode(',', ApplicationService::STAGES),
            'note' => 'nullable|string|max:1000',
        ]);
    }
}
