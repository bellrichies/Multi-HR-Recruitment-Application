<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Requests;

use App\Core\HttpException;
use App\Core\Request;
use App\Core\Validator;

class AddQuestionRequest
{
    public function __construct(private readonly Validator $validator)
    {
    }

    public function validate(Request $request): array
    {
        $data = $this->validator->validate($request->all(), [
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:multiple_choice,essay',
            'score' => 'required|numeric',
        ]);
        $payload = $request->all();

        if ($data['question_type'] === 'multiple_choice' && (! isset($payload['options'], $payload['correct_answer']))) {
            throw new HttpException('Multiple-choice questions require options and correct_answer.', 422, [
                'options' => ['Options are required for multiple-choice questions.'],
                'correct_answer' => ['Correct answer is required for multiple-choice questions.'],
            ]);
        }

        if ((float) $data['score'] <= 0) {
            throw new HttpException('Question score must be greater than zero.', 422, [
                'score' => ['Question score must be greater than zero.'],
            ]);
        }

        $data['options'] = $payload['options'] ?? null;
        $data['correct_answer'] = $payload['correct_answer'] ?? null;

        return $data;
    }
}
