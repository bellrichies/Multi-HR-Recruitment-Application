<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Requests;

use App\Core\Request;
use App\Core\Validator;

class SendMessageRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'message_body' => 'required|string',
            'attachment_path' => 'nullable|string|max:500',
        ]);
    }
}
