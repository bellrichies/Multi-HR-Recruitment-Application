<?php

declare(strict_types=1);

namespace App\Modules\Recruiters\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Recruiters\Requests\ReviewRecruiterRequest;
use App\Modules\Recruiters\Requests\UpdateRecruiterProfileRequest;
use App\Modules\Recruiters\Requests\UploadRecruiterDocumentRequest;
use App\Modules\Recruiters\Resources\RecruiterProfileResource;
use App\Modules\Recruiters\Services\RecruiterProfileService;

class RecruiterProfileController extends Controller
{
    public function __construct(
        private readonly RecruiterProfileService $profiles,
        private readonly UpdateRecruiterProfileRequest $updateRequest,
        private readonly UploadRecruiterDocumentRequest $uploadRequest,
        private readonly ReviewRecruiterRequest $reviewRequest
    ) {
    }

    public function mine(Request $request): void
    {
        $result = $this->profiles->mine((int) $request->user()['id']);

        $this->success(
            RecruiterProfileResource::make($result['profile'], $result['documents']),
            'Recruiter profile retrieved successfully.'
        );
    }

    public function update(Request $request): void
    {
        $result = $this->profiles->update((int) $request->user()['id'], $this->updateRequest->validate($request), $this->context($request));

        $this->success(
            RecruiterProfileResource::make($result['profile'], $result['documents']),
            'Recruiter profile updated successfully.'
        );
    }

    public function uploadDocument(Request $request): void
    {
        $document = $this->profiles->uploadDocument((int) $request->user()['id'], $this->uploadRequest->validate($request), $this->context($request));

        $this->success([
            'id' => (int) $document['id'],
            'document_type' => $document['document_type'],
            'status' => $document['status'],
            'created_at' => $document['created_at'],
        ], 'Recruiter document uploaded successfully.', [], 201);
    }

    public function show(Request $request, string $id): void
    {
        $result = $this->profiles->show((int) $id);

        $this->success(
            RecruiterProfileResource::make($result['profile'], $result['documents']),
            'Recruiter profile retrieved successfully.'
        );
    }

    public function verify(Request $request, string $id): void
    {
        $result = $this->profiles->review((int) $id, 'verified', (int) $request->user()['id'], null, $this->context($request));

        $this->success(
            RecruiterProfileResource::make($result['profile'], $result['documents']),
            'Recruiter verified successfully.'
        );
    }

    public function reject(Request $request, string $id): void
    {
        $data = $this->reviewRequest->validate($request);
        $result = $this->profiles->review((int) $id, 'rejected', (int) $request->user()['id'], $data['reason'] ?? null, $this->context($request));

        $this->success(
            RecruiterProfileResource::make($result['profile'], $result['documents']),
            'Recruiter rejected successfully.'
        );
    }

    private function context(Request $request): array
    {
        return [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
