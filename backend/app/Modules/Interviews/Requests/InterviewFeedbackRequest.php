<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Requests;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Validator;

class InterviewFeedbackRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'rating' => 'nullable|integer',
            'feedback' => 'required|string',
            'recommendation' => 'required|string|in:advance,reject,hold,hire',
        ]);

        if (isset($data['rating']) && ((int) $data['rating'] < 1 || (int) $data['rating'] > 5)) {
            throw new HttpException('Interview rating must be between 1 and 5.', 422, [
                'rating' => ['Rating must be between 1 and 5.'],
            ]);
        }

        return $data;
    }
}
