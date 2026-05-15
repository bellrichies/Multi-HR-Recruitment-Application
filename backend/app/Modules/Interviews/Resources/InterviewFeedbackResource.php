<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Resources;

class InterviewFeedbackResource
{
    public static function collection(array $feedback): array
    {
        return array_map(fn (array $item): array => [
            'id' => (int) $item['id'],
            'interview_id' => (int) $item['interview_id'],
            'submitted_by' => (int) $item['submitted_by'],
            'rating' => $item['rating'] === null ? null : (int) $item['rating'],
            'feedback' => $item['feedback'],
            'recommendation' => $item['recommendation'],
            'created_at' => $item['created_at'],
        ], $feedback);
    }
}
