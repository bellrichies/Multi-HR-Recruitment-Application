<?php

declare(strict_types=1);

namespace App\Modules\Matching\Resources;

class CandidateUnlockResource
{
    public static function make(array $unlock): array
    {
        return [
            'id' => (int) $unlock['id'],
            'recruiter_id' => (int) $unlock['recruiter_id'],
            'job_seeker_id' => (int) $unlock['job_seeker_id'],
            'job_id' => $unlock['job_id'] === null ? null : (int) $unlock['job_id'],
            'transaction_id' => $unlock['transaction_id'] === null ? null : (int) $unlock['transaction_id'],
            'unlocked_by' => $unlock['unlocked_by'] === null ? null : (int) $unlock['unlocked_by'],
            'expires_at' => $unlock['expires_at'],
            'created_at' => $unlock['created_at'],
        ];
    }
}
