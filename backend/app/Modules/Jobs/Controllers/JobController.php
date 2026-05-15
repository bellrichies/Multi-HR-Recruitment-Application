<?php

declare(strict_types=1);

namespace App\Modules\Jobs\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Jobs\Requests\AssignJobRequest;
use App\Modules\Jobs\Requests\CreateJobRequest;
use App\Modules\Jobs\Requests\UpdateJobRequest;
use App\Modules\Jobs\Resources\JobResource;
use App\Modules\Jobs\Services\JobService;

class JobController extends Controller
{
    public function __construct(
        private readonly JobService $jobs,
        private readonly CreateJobRequest $createRequest,
        private readonly UpdateJobRequest $updateRequest,
        private readonly AssignJobRequest $assignRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->jobs->list($request->user(), $request->query());

        $this->success(JobResource::collection($result['data']), 'Jobs retrieved successfully.', $result['meta']);
    }

    public function publicIndex(Request $request): void
    {
        $result = $this->jobs->publicList($request->query());

        $this->success(JobResource::collection($result['data']), 'Public jobs retrieved successfully.', $result['meta']);
    }

    public function store(Request $request): void
    {
        $result = $this->jobs->create($this->createRequest->validate($request), $request->user(), $this->context($request));

        $this->success(
            JobResource::make($result['job'], $result['skills'], $result['assignments'], $result['status_logs']),
            'Job created successfully.',
            [],
            201
        );
    }

    public function show(Request $request, string $id): void
    {
        $result = $this->jobs->show((int) $id, $request->user());

        $this->success(
            JobResource::make($result['job'], $result['skills'], $result['assignments'], $result['status_logs']),
            'Job retrieved successfully.'
        );
    }

    public function publicShow(Request $request, string $slug): void
    {
        $result = $this->jobs->publicShow($slug);

        $this->success(
            JobResource::make($result['job'], $result['skills'], $result['assignments'], $result['status_logs']),
            'Public job retrieved successfully.'
        );
    }

    public function update(Request $request, string $id): void
    {
        $result = $this->jobs->update((int) $id, $this->updateRequest->validate($request), $request->user(), $this->context($request));

        $this->success(
            JobResource::make($result['job'], $result['skills'], $result['assignments'], $result['status_logs']),
            'Job updated successfully.'
        );
    }

    public function submitForApproval(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->submitForApproval((int) $id, $request->user(), $this->context($request)), 'Job submitted for approval successfully.');
    }

    public function approve(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->approve((int) $id, $request->user(), $this->context($request)), 'Job approved successfully.');
    }

    public function publish(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->publish((int) $id, $request->user(), $this->context($request)), 'Job published successfully.');
    }

    public function pause(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->pause((int) $id, $request->user(), $this->context($request)), 'Job paused successfully.');
    }

    public function close(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->close((int) $id, $request->user(), $this->context($request)), 'Job closed successfully.');
    }

    public function cancel(Request $request, string $id): void
    {
        $this->statusResponse($this->jobs->cancel((int) $id, $request->user(), $this->context($request)), 'Job cancelled successfully.');
    }

    public function assignHrOfficer(Request $request, string $id): void
    {
        $data = $this->assignRequest->validate($request);
        $result = $this->jobs->assign((int) $id, (int) $data['user_id'], 'hr_officer', $request->user(), $this->context($request));

        $this->statusResponse($result, 'HR officer assigned successfully.');
    }

    public function assignRelationshipOfficer(Request $request, string $id): void
    {
        $data = $this->assignRequest->validate($request);
        $result = $this->jobs->assign((int) $id, (int) $data['user_id'], 'relationship_officer', $request->user(), $this->context($request));

        $this->statusResponse($result, 'Relationship officer assigned successfully.');
    }

    private function statusResponse(array $result, string $message): void
    {
        $this->success(JobResource::make($result['job'], $result['skills'], $result['assignments'], $result['status_logs']), $message);
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
