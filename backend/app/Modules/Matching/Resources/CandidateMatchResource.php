<?php

declare(strict_types=1);

namespace App\Modules\Matching\Resources;

class CandidateMatchResource
{
    public static function make(array $match): array
    {
        return [
            'id' => (int) $match['id'],
            'job_id' => (int) $match['job_id'],
            'job_seeker_id' => (int) $match['job_seeker_id'],
            'matched_by' => $match['matched_by'] === null ? null : (int) $match['matched_by'],
            'match_score' => (float) $match['match_score'],
            'match_reason' => $match['match_reason'],
            'status' => $match['status'],
            'created_at' => $match['created_at'],
            'updated_at' => $match['updated_at'],
        ];
    }

    public static function collection(array $matches): array
    {
        return array_map(fn (array $match): array => self::make($match), $matches);
    }
}
