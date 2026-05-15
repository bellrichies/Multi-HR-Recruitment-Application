<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Recruiters\Repositories\RecruiterDocumentRepository;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;
use App\Support\FileUpload;

class RecruiterProfileService
{
    public function __construct(
        private readonly RecruiterProfileRepository $profiles,
        private readonly RecruiterDocumentRepository $documents,
        private readonly FileUpload $uploads,
        private readonly AuditLogService $audit,
        private readonly NotificationService $notifications
    ) {
    }

    public function mine(int $userId): array
    {
        $profile = $this->profiles->findByUserId($userId);

        return [
            'profile' => $profile,
            'documents' => $profile === null ? [] : $this->documents->forProfile((int) $profile['id']),
        ];
    }

    public function show(int $id): array
    {
        $profile = $this->profiles->findById($id);

        if ($profile === null) {
            throw new HttpException('Recruiter profile not found.', 404);
        }

        return [
            'profile' => $profile,
            'documents' => $this->documents->forProfile((int) $profile['id']),
        ];
    }

    public function update(int $userId, array $data, array $context): array
    {
        $old = $this->profiles->findByUserId($userId);
        $profile = $this->profiles->upsert($userId, $data);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $userId,
            'action' => 'recruiters.profile_updated',
            'module' => 'recruiters',
            'entity_type' => 'recruiter_profile',
            'entity_id' => (int) $profile['id'],
            'old_values' => $old,
            'new_values' => $profile,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $this->mine($userId);
    }

    public function uploadDocument(int $userId, array $data, array $context): array
    {
        $profile = $this->profiles->findByUserId($userId);

        if ($profile === null) {
            throw new HttpException('Complete recruiter profile before uploading documents.', 422);
        }

        $path = $this->uploads->store($data['file'], 'recruiter-documents/' . $profile['id']);
        $document = $this->documents->create((int) $profile['id'], $data['document_type'], $path);

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $userId,
            'action' => 'recruiters.document_uploaded',
            'module' => 'recruiters',
            'entity_type' => 'recruiter_document',
            'entity_id' => (int) $document['id'],
            'new_values' => ['document_type' => $document['document_type'], 'status' => $document['status']],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $document;
    }

    public function review(int $profileId, string $status, int $reviewerId, ?string $reason, array $context): array
    {
        $old = $this->profiles->findById($profileId);

        if ($old === null) {
            throw new HttpException('Recruiter profile not found.', 404);
        }

        $profile = $this->profiles->updateVerification($profileId, $status, $reviewerId, $reason);
        $this->notifications->notify(
            (int) $profile['user_id'],
            $status === 'verified' ? 'Account approved' : 'Account verification updated',
            $status === 'verified' ? 'Your recruiter account has been approved.' : 'Your recruiter account verification status has changed.',
            $status === 'verified' ? 'account_approved' : 'account_suspended',
            ['recruiter_id' => (int) $profile['id'], 'status' => $status],
        );

        $this->audit->record([
            'actor_id' => $reviewerId,
            'action' => 'recruiters.verification_' . $status,
            'module' => 'recruiters',
            'entity_type' => 'recruiter_profile',
            'entity_id' => $profileId,
            'old_values' => ['verification_status' => $old['verification_status']],
            'new_values' => ['verification_status' => $profile['verification_status'], 'reason' => $reason],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $this->show($profileId);
    }
}
