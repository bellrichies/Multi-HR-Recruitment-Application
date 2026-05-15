<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Requests;

use App\Core\Request;
use App\Core\Validator;

class CreateConversationRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        return $this->validator->validate($request->all(), [
            'participant_user_id' => 'required|integer',
            'conversation_type' => 'required|string|in:direct,job_context,interview_request',
            'subject' => 'nullable|string|max:180',
            'job_id' => 'nullable|integer',
            'message_body' => 'nullable|string',
        ]);
    }
}
