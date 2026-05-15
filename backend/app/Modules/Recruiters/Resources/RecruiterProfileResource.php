<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Resources;

class RecruiterProfileResource
{
    public static function make(?array $profile, array $documents = []): ?array
    {
        if ($profile === null) {
            return null;
        }

        return [
            'id' => (int) $profile['id'],
            'user_id' => (int) $profile['user_id'],
            'company_name' => $profile['company_name'],
            'company_email' => $profile['company_email'],
            'company_phone' => $profile['company_phone'],
            'company_website' => $profile['company_website'],
            'industry' => $profile['industry'],
            'company_size' => $profile['company_size'],
            'rc_number' => $profile['rc_number'],
            'address' => $profile['address'],
            'verification_status' => $profile['verification_status'],
            'is_complete' => self::isComplete($profile),
            'verified_at' => $profile['verified_at'],
            'documents' => array_map(fn (array $document): array => [
                'id' => (int) $document['id'],
                'document_type' => $document['document_type'],
                'status' => $document['status'],
                'reviewed_at' => $document['reviewed_at'],
                'rejection_reason' => $document['rejection_reason'] ?? null,
                'created_at' => $document['created_at'],
            ], $documents),
            'created_at' => $profile['created_at'],
            'updated_at' => $profile['updated_at'],
        ];
    }

    private static function isComplete(array $profile): bool
    {
        foreach (['company_name', 'company_email', 'company_phone', 'industry', 'company_size', 'address'] as $field) {
            if (empty($profile[$field])) {
                return false;
            }
        }

        return true;
    }
}
