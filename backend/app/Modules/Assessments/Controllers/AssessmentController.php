<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Assessments\Requests\AddQuestionRequest;
use App\Modules\Assessments\Requests\AssignAssessmentRequest;
use App\Modules\Assessments\Requests\CreateAssessmentRequest;
use App\Modules\Assessments\Requests\GradeAssessmentRequest;
use App\Modules\Assessments\Requests\SubmitAssessmentRequest;
use App\Modules\Assessments\Resources\AssessmentAssignmentResource;
use App\Modules\Assessments\Resources\AssessmentQuestionResource;
use App\Modules\Assessments\Resources\AssessmentResource;
use App\Modules\Assessments\Services\AssessmentService;

class AssessmentController extends Controller
{
    public function __construct(
        private readonly AssessmentService $assessments,
        private readonly CreateAssessmentRequest $assessmentRequest,
        private readonly AddQuestionRequest $questionRequest,
        private readonly AssignAssessmentRequest $assignRequest,
        private readonly SubmitAssessmentRequest $submitRequest,
        private readonly GradeAssessmentRequest $gradeRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->assessments->list($request->user(), $request->query());
        $data = $result['data'];
        $resource = isset($data[0]['assessment_id'])
            ? AssessmentAssignmentResource::collection($data)
            : AssessmentResource::collection($data);

        $this->success($resource, 'Assessments retrieved successfully.', $result['meta']);
    }

    public function store(Request $request): void
    {
        $assessment = $this->assessments->create($this->assessmentRequest->validate($request), $request->user(), $this->context($request));

        $this->success(AssessmentResource::make($assessment), 'Assessment created successfully.', [], 201);
    }

    public function show(Request $request, string $id): void
    {
        $payload = $this->assessments->show((int) $id);

        $this->success(AssessmentResource::make($payload['assessment'], $payload['questions']), 'Assessment retrieved successfully.');
    }

    public function update(Request $request, string $id): void
    {
        $assessment = $this->assessments->update((int) $id, $this->assessmentRequest->validate($request), $this->context($request));

        $this->success(AssessmentResource::make($assessment), 'Assessment updated successfully.');
    }

    public function addQuestion(Request $request, string $id): void
    {
        $question = $this->assessments->addQuestion((int) $id, $this->questionRequest->validate($request), $this->context($request));

        $this->success(AssessmentQuestionResource::make($question, true), 'Assessment question added successfully.', [], 201);
    }

    public function assign(Request $request, string $id): void
    {
        $payload = $this->assessments->assign((int) $id, $this->assignRequest->validate($request), $request->user(), $this->context($request));

        $this->success(AssessmentAssignmentResource::make($payload['assignment'], $payload['result'], $payload['answers']), 'Assessment assigned successfully.', [], 201);
    }

    public function start(Request $request, string $id): void
    {
        $payload = $this->assessments->start((int) $id, $request->user());

        $this->success(AssessmentAssignmentResource::make($payload['assignment'], $payload['result'], $payload['answers']), 'Assessment started successfully.');
    }

    public function submit(Request $request, string $id): void
    {
        $payload = $this->assessments->submit((int) $id, $this->submitRequest->validate($request), $request->user(), $this->context($request));

        $this->success(AssessmentAssignmentResource::make($payload['assignment'], $payload['result'], $payload['answers']), 'Assessment submitted successfully.');
    }

    public function grade(Request $request, string $id): void
    {
        $payload = $this->assessments->grade((int) $id, $this->gradeRequest->validate($request), $request->user(), $this->context($request));

        $this->success(AssessmentAssignmentResource::make($payload['assignment'], $payload['result'], $payload['answers']), 'Assessment graded successfully.');
    }

    private function context(Request $request): array
    {
        return ['actor_id' => $request->user()['id'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
