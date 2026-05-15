<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\HR\Repositories\HrOfficerProfileRepository;
use App\Modules\JobSeekers\Repositories\JobSeekerDetailRepository;
use App\Modules\JobSeekers\Repositories\JobSeekerProfileRepository;
use App\Support\FileUpload;

class JobSeekerProfileService
{
    public function __construct(
        private readonly JobSeekerProfileRepository $profiles,
        private readonly JobSeekerDetailRepository $details,
        private readonly HrOfficerProfileRepository $hrProfiles,
        private readonly FileUpload $uploads,
        private readonly AuditLogService $audit
    ) {
    }

    public function mine(int $userId): array
    {
        $profile = $this->profiles->findByUserId($userId);

        return [
            'profile' => $profile,
            'details' => $profile === null ? [] : $this->details->allForProfile((int) $profile['id']),
        ];
    }

    public function show(int $id): array
    {
        $profile = $this->profiles->findById($id);

        if ($profile === null) {
            throw new HttpException('Job seeker profile not found.', 404);
        }

        return ['profile' => $profile, 'details' => $this->details->allForProfile((int) $profile['id'])];
    }

    public function update(int $userId, array $data, array $context): array
    {
        if (! empty($data['referral_code'])) {
            $hrProfile = $this->hrProfiles->findByReferralCode((string) $data['referral_code']);

            if ($hrProfile === null) {
                throw new HttpException('HR referral code is invalid.', 422, [
                    'referral_code' => ['HR referral code is invalid.'],
                ]);
            }

            $data['referred_by_hr_officer_id'] = (int) $hrProfile['user_id'];
            $data['assigned_hr_officer_id'] = (int) $hrProfile['user_id'];
        }

        $old = $this->profiles->findByUserId($userId);
        $profile = $this->profiles->upsert($userId, $data);
        $this->refreshCompletion((int) $profile['id']);
        $profile = $this->profiles->findByUserId($userId);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $userId,
            'action' => 'job_seekers.profile_updated',
            'module' => 'job_seekers',
            'entity_type' => 'job_seeker_profile',
            'entity_id' => (int) $profile['id'],
            'old_values' => $old,
            'new_values' => $profile,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $this->mine($userId);
    }

    public function addDetail(int $userId, string $type, array $data, array $context): array
    {
        $profile = $this->requireProfile($userId);

        if (isset($data['file'])) {
            $data['file_path'] = $this->uploads->store($data['file'], 'job-seeker-documents/' . $profile['id']);
            $data['document_path'] = $data['file_path'];
        }

        $record = match ($type) {
            'skill' => $this->details->createSkill((int) $profile['id'], $data),
            'work_experience' => $this->details->createWorkExperience((int) $profile['id'], $data),
            'education' => $this->details->createEducation((int) $profile['id'], $data),
            'certification' => $this->details->createCertification((int) $profile['id'], $data),
            'document' => $this->details->createDocument((int) $profile['id'], $data['document_type'], $data['file_path']),
            'guarantor' => $this->details->createGuarantor((int) $profile['id'], $data),
            default => throw new HttpException('Unsupported job seeker detail type.', 500),
        };

        $this->refreshCompletion((int) $profile['id']);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $userId,
            'action' => 'job_seekers.' . $type . '_created',
            'module' => 'job_seekers',
            'entity_type' => $type,
            'entity_id' => (int) $record['id'],
            'new_values' => ['profile_id' => (int) $profile['id']],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $record;
    }

    public function reviewDocument(int $documentId, string $status, int $reviewerId, array $context): array
    {
        $document = $this->details->reviewDocument($documentId, $status, $reviewerId);

        $this->audit->record([
            'actor_id' => $reviewerId,
            'action' => 'job_seekers.document_' . $status,
            'module' => 'job_seekers',
            'entity_type' => 'job_seeker_document',
            'entity_id' => (int) $document['id'],
            'new_values' => ['status' => $status],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        unset($document['file_path']);

        return $document;
    }

    private function requireProfile(int $userId): array
    {
        $profile = $this->profiles->findByUserId($userId);

        if ($profile === null) {
            throw new HttpException('Create job seeker profile before adding onboarding details.', 422);
        }

        return $profile;
    }

    private function refreshCompletion(int $profileId): void
    {
        $profile = $this->profiles->findById($profileId);
        $counts = $this->profiles->counts($profileId);
        $steps = [];

        $hasProfile = $profile !== null
            && ! empty($profile['location'])
            && ! empty($profile['current_job_title'])
            && ! empty($profile['availability_status']);

        if ($hasProfile) {
            $steps[] = 'profile';
        }

        foreach ($counts as $key => $count) {
            if ($count > 0) {
                $steps[] = $key;
            }
        }

        $required = ['profile', 'skills', 'work_experiences', 'educations', 'documents', 'guarantors'];
        $percentage = (int) floor((count(array_intersect($required, $steps)) / count($required)) * 100);

        $this->profiles->updateCompletion($profileId, $percentage, $steps);
    }
}
