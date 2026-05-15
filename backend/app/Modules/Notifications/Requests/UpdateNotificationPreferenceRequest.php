<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Requests;

use App\Core\Request;
use App\Core\Validator;

class UpdateNotificationPreferenceRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'in_app_enabled' => 'required|integer',
            'email_enabled' => 'required|integer',
        ]);
        $data['event_preferences'] = is_array($request->input('event_preferences')) ? $request->input('event_preferences') : [];

        return $data;
    }
}
