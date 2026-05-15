<?php

declare(strict_types=1);

namespace App\Modules\Applications\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Applications\Requests\ApplyJobRequest;
use App\Modules\Applications\Requests\MoveStageRequest;
use App\Modules\Applications\Resources\ApplicationResource;
use App\Modules\Applications\Services\ApplicationService;

class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationService $applications,
        private readonly ApplyJobRequest $applyRequest,
        private readonly MoveStageRequest $moveStageRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->applications->list($request->user(), $request->query());

        $this->success(ApplicationResource::collection($result['data']), 'Applications retrieved successfully.', $result['meta']);
    }

    public function apply(Request $request, string $jobId): void
    {
        $result = $this->applications->apply((int) $jobId, $this->applyRequest->validate($request), $request->user(), $this->context($request));

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application submitted successfully.', [], 201);
    }

    public function show(Request $request, string $id): void
    {
        $result = $this->applications->show((int) $id, $request->user());

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application retrieved successfully.');
    }

    public function moveStage(Request $request, string $id): void
    {
        $data = $this->moveStageRequest->validate($request);
        $result = $this->applications->moveStage((int) $id, $data['stage'], $data['note'] ?? null, $request->user(), $this->context($request));

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application stage updated successfully.');
    }

    public function shortlist(Request $request, string $id): void
    {
        $result = $this->applications->shortlist((int) $id, $request->user(), $this->context($request));

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application shortlisted successfully.');
    }

    public function reject(Request $request, string $id): void
    {
        $result = $this->applications->reject((int) $id, $request->user(), $this->context($request));

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application rejected successfully.');
    }

    public function withdraw(Request $request, string $id): void
    {
        $result = $this->applications->withdraw((int) $id, $request->user(), $this->context($request));

        $this->success(ApplicationResource::make($result['application'], $result['logs']), 'Application withdrawn successfully.');
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
