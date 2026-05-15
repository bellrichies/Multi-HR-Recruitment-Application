<?php

declare(strict_types=1);

namespace App\Modules\RelationshipOfficers\Resources;

class RelationshipOfficerProfileResource
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
            'assigned_employer_count' => (int) $profile['assigned_employer_count'],
            'assigned_job_count' => (int) $profile['assigned_job_count'],
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['updated_at'],
        ];
    }
}
