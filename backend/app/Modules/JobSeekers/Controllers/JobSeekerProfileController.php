<?php

declare(strict_types=1);

namespace App\Modules\JobSeekers\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\JobSeekers\Requests\JobSeekerDetailRequest;
use App\Modules\JobSeekers\Requests\UpdateJobSeekerProfileRequest;
use App\Modules\JobSeekers\Resources\JobSeekerProfileResource;
use App\Modules\JobSeekers\Services\JobSeekerProfileService;

class JobSeekerProfileController extends Controller
{
    public function __construct(
        private readonly JobSeekerProfileService $profiles,
        private readonly UpdateJobSeekerProfileRequest $updateRequest,
        private readonly JobSeekerDetailRequest $detailRequest
    ) {
    }

    public function mine(Request $request): void
    {
        $result = $this->profiles->mine((int) $request->user()['id']);

        $this->success(
            JobSeekerProfileResource::make($result['profile'], $result['details']),
            'Job seeker profile retrieved successfully.'
        );
    }

    public function update(Request $request): void
    {
        $result = $this->profiles->update((int) $request->user()['id'], $this->updateRequest->validate($request), $this->context($request));

        $this->success(
            JobSeekerProfileResource::make($result['profile'], $result['details']),
            'Job seeker profile updated successfully.'
        );
    }

    public function show(Request $request, string $id): void
    {
        $result = $this->profiles->show((int) $id);

        $this->success(
            JobSeekerProfileResource::make($result['profile'], $result['details']),
            'Job seeker profile retrieved successfully.'
        );
    }

    public function addSkill(Request $request): void
    {
        $this->created($request, 'skill', $this->detailRequest->skill($request), 'Skill added successfully.');
    }

    public function addWorkExperience(Request $request): void
    {
        $this->created($request, 'work_experience', $this->detailRequest->workExperience($request), 'Work experience added successfully.');
    }

    public function addEducation(Request $request): void
    {
        $this->created($request, 'education', $this->detailRequest->education($request), 'Education added successfully.');
    }

    public function addCertification(Request $request): void
    {
        $this->created($request, 'certification', $this->detailRequest->certification($request), 'Certification added successfully.');
    }

    public function uploadDocument(Request $request): void
    {
        $this->created($request, 'document', $this->detailRequest->document($request), 'Document uploaded successfully.');
    }

    public function addGuarantor(Request $request): void
    {
        $this->created($request, 'guarantor', $this->detailRequest->guarantor($request), 'Guarantor added successfully.');
    }

    public function reviewDocument(Request $request, string $id): void
    {
        $data = $this->detailRequest->reviewDocument($request);
        $document = $this->profiles->reviewDocument((int) $id, $data['status'], (int) $request->user()['id'], $this->context($request));

        $this->success($document, 'Job seeker document reviewed successfully.');
    }

    private function created(Request $request, string $type, array $data, string $message): void
    {
        $record = $this->profiles->addDetail((int) $request->user()['id'], $type, $data, $this->context($request));
        unset($record['file_path'], $record['document_path']);

        $this->success($record, $message, [], 201);
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
