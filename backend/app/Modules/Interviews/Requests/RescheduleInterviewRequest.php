<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Requests;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Validator;

class RescheduleInterviewRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'interview_type' => 'required|string|in:video,phone,physical',
            'meeting_link' => 'nullable|string|max:500',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer',
        ]);

        if ((int) $data['duration_minutes'] <= 0) {
            throw new HttpException('Interview duration must be greater than zero.', 422, [
                'duration_minutes' => ['Duration must be greater than zero.'],
            ]);
        }

        return $data;
    }
}
