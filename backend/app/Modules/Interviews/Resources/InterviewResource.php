<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Resources;

class InterviewResource
{
    public static function make(array $interview, array $feedback = []): array
    {
        return [
            'id' => (int) $interview['id'],
            'job_id' => (int) $interview['job_id'],
            'application_id' => (int) $interview['application_id'],
            'job_seeker_id' => (int) $interview['job_seeker_id'],
            'recruiter_id' => (int) $interview['recruiter_id'],
            'scheduled_by' => (int) $interview['scheduled_by'],
            'interview_type' => $interview['interview_type'],
            'meeting_link' => $interview['meeting_link'],
            'scheduled_at' => $interview['scheduled_at'],
            'duration_minutes' => (int) $interview['duration_minutes'],
            'status' => $interview['status'],
            'feedback' => InterviewFeedbackResource::collection($feedback),
            'created_at' => $interview['created_at'],
            'updated_at' => $interview['updated_at'],
        ];
    }

    public static function collection(array $interviews): array
    {
        return array_map(fn (array $interview): array => self::make($interview), $interviews);
    }
}
