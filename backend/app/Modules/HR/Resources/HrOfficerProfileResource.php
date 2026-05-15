<?php

declare(strict_types=1);

namespace App\Modules\HR\Resources;

class HrOfficerProfileResource
{
    public static function make(?array $profile): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'id' => (int) $profile['id'],
            'user_id' => (int) $profile['user_id'],
            'employee_code' => $profile['employee_code'],
            'referral_code' => $profile['referral_code'],
            'performance_score' => (float) $profile['performance_score'],
            'active_candidate_count' => (int) $profile['active_candidate_count'],
            'successful_placements_count' => (int) $profile['successful_placements_count'],
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['updated_at'],
        ];
    }
}
