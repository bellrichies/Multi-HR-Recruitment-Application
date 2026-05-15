<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Interviews\Requests\InterviewFeedbackRequest;
use App\Modules\Interviews\Requests\RescheduleInterviewRequest;
use App\Modules\Interviews\Requests\ScheduleInterviewRequest;
use App\Modules\Interviews\Resources\InterviewResource;
use App\Modules\Interviews\Services\InterviewService;

class InterviewController extends Controller
{
    public function __construct(
        private readonly InterviewService $interviews,
        private readonly ScheduleInterviewRequest $scheduleRequest,
        private readonly RescheduleInterviewRequest $rescheduleRequest,
        private readonly InterviewFeedbackRequest $feedbackRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->interviews->list($request->user(), $request->query());

        $this->success(InterviewResource::collection($result['data']), 'Interviews retrieved successfully.', $result['meta']);
    }

    public function store(Request $request): void
    {
        $payload = $this->interviews->schedule($this->scheduleRequest->validate($request), $request->user(), $this->context($request));

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview scheduled successfully.', [], 201);
    }

    public function show(Request $request, string $id): void
    {
        $payload = $this->interviews->show((int) $id, $request->user());

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview retrieved successfully.');
    }

    public function update(Request $request, string $id): void
    {
        $payload = $this->interviews->update((int) $id, $this->rescheduleRequest->validate($request), $request->user(), $this->context($request));

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview updated successfully.');
    }

    public function reschedule(Request $request, string $id): void
    {
        $payload = $this->interviews->reschedule((int) $id, $this->rescheduleRequest->validate($request), $request->user(), $this->context($request));

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview rescheduled successfully.');
    }

    public function cancel(Request $request, string $id): void
    {
        $payload = $this->interviews->cancel((int) $id, $request->user(), $this->context($request));

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview cancelled successfully.');
    }

    public function feedback(Request $request, string $id): void
    {
        $payload = $this->interviews->submitFeedback((int) $id, $this->feedbackRequest->validate($request), $request->user(), $this->context($request));

        $this->success(InterviewResource::make($payload['interview'], $payload['feedback']), 'Interview feedback submitted successfully.', [], 201);
    }

    private function context(Request $request): array
    {
        return ['actor_id' => $request->user()['id'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
