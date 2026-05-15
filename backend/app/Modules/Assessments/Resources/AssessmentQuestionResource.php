<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Resources;

class AssessmentQuestionResource
{
    public static function make(array $question, bool $includeCorrectAnswer = false): array
    {
        $data = [
            'id' => (int) $question['id'],
            'assessment_id' => (int) $question['assessment_id'],
            'question_text' => $question['question_text'],
            'question_type' => $question['question_type'],
            'options' => $question['options_json'] === null ? null : json_decode((string) $question['options_json'], true),
            'score' => (float) $question['score'],
            'created_at' => $question['created_at'],
        ];

        if ($includeCorrectAnswer) {
            $data['correct_answer'] = $question['correct_answer_json'] === null ? null : json_decode((string) $question['correct_answer_json'], true);
        }

        return $data;
    }

    public static function collection(array $questions, bool $includeCorrectAnswer = false): array
    {
        return array_map(fn (array $question): array => self::make($question, $includeCorrectAnswer), $questions);
    }
}
