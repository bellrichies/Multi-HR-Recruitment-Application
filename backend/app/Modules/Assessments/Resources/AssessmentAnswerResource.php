<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Resources;

class AssessmentAnswerResource
{
    public static function collection(array $answers): array
    {
        return array_map(fn (array $answer): array => [
            'id' => (int) $answer['id'],
            'assignment_id' => (int) $answer['assignment_id'],
            'question_id' => (int) $answer['question_id'],
            'answer' => json_decode((string) $answer['answer_json'], true),
            'score_awarded' => $answer['score_awarded'] === null ? null : (float) $answer['score_awarded'],
            'created_at' => $answer['created_at'],
        ], $answers);
    }
}
