<?php

declare(strict_types=1);

namespace App\Modules\Assessments\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Applications\Repositories\ApplicationRepository;
use App\Modules\Applications\Repositories\ApplicationStageLogRepository;
use App\Modules\Applications\Services\ApplicationService;
use App\Modules\Assessments\Repositories\AssessmentAnswerRepository;
use App\Modules\Assessments\Repositories\AssessmentAssignmentRepository;
use App\Modules\Assessments\Repositories\AssessmentQuestionRepository;
use App\Modules\Assessments\Repositories\AssessmentRepository;
use App\Modules\Assessments\Repositories\AssessmentResultRepository;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\JobSeekers\Repositories\JobSeekerProfileRepository;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;

class AssessmentService
{
    public function __construct(
        private readonly AssessmentRepository $assessments,
        private readonly AssessmentQuestionRepository $questions,
        private readonly AssessmentAssignmentRepository $assignments,
        private readonly AssessmentAnswerRepository $answers,
        private readonly AssessmentResultRepository $results,
        private readonly JobSeekerProfileRepository $profiles,
        private readonly ApplicationRepository $applications,
        private readonly ApplicationStageLogRepository $stageLogs,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly AuditLogService $audit,
        private readonly NotificationService $notifications
    ) {
    }

    public function list(array $user, array $filters): array
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('job_seeker', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $profile = $this->profiles->findByUserId((int) $user['id']);
            $filters['job_seeker_id'] = $profile['id'] ?? 0;

            return $this->assignments->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
        }

        if (in_array('recruiter', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);
            $filters['recruiter_id'] = $recruiter['id'] ?? 0;

            return $this->assignments->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
        }

        return $this->assessments->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function create(array $data, array $user, array $context): array
    {
        return Database::transaction(function () use ($data, $user, $context): array {
            $assessment = $this->assessments->create($data + ['created_by' => (int) $user['id']]);
            $this->auditRecord($context, 'assessments.create', 'assessment', (int) $assessment['id'], null, $assessment);

            return $assessment;
        });
    }

    public function update(int $id, array $data, array $context): array
    {
        $old = $this->requireAssessment($id);

        return Database::transaction(function () use ($id, $data, $context, $old): array {
            $assessment = $this->assessments->update($id, $data);
            $this->auditRecord($context, 'assessments.update', 'assessment', $id, $old, $assessment);

            return $assessment;
        });
    }

    public function show(int $id): array
    {
        $assessment = $this->requireAssessment($id);

        return ['assessment' => $assessment, 'questions' => $this->questions->forAssessment($id)];
    }

    public function addQuestion(int $assessmentId, array $data, array $context): array
    {
        $this->requireAssessment($assessmentId);

        return Database::transaction(function () use ($assessmentId, $data, $context): array {
            $question = $this->questions->create($data + ['assessment_id' => $assessmentId]);
            $this->auditRecord($context, 'assessments.questions.create', 'assessment_question', (int) $question['id'], null, $question);

            return $question;
        });
    }

    public function assign(int $assessmentId, array $data, array $user, array $context): array
    {
        $this->requireAssessment($assessmentId);
        $profile = $this->profiles->findById((int) $data['job_seeker_id']);

        if ($profile === null) {
            throw new HttpException('Job seeker profile not found.', 404);
        }

        $application = null;

        if (! empty($data['application_id'])) {
            $application = $this->applications->findById((int) $data['application_id']);

            if ($application === null || (int) $application['job_seeker_id'] !== (int) $profile['id']) {
                throw new HttpException('Application does not match the selected candidate.', 422);
            }

            if (! empty($data['job_id']) && (int) $data['job_id'] !== (int) $application['job_id']) {
                throw new HttpException('Job does not match the selected application.', 422);
            }

            $data['job_id'] = (int) $application['job_id'];
        }

        if ($this->assignments->findExisting($assessmentId, (int) $profile['id'], isset($data['job_id']) ? (int) $data['job_id'] : null, isset($data['application_id']) ? (int) $data['application_id'] : null) !== null) {
            throw new HttpException('This assessment has already been assigned to this candidate for the selected context.', 409);
        }

        return Database::transaction(function () use ($assessmentId, $data, $user, $context, $application): array {
            $assignment = $this->assignments->create($data + [
                'assessment_id' => $assessmentId,
                'assigned_by' => (int) $user['id'],
            ]);

            if ($application !== null) {
                $this->advanceApplication($application, 'assessment_invited', (int) $user['id'], 'Assessment assigned.');
            }

            $profile = $this->profiles->findById((int) $assignment['job_seeker_id']);

            if ($profile !== null) {
                $this->notifications->notify((int) $profile['user_id'], 'Assessment assigned', 'You have been assigned an assessment.', 'assessment_assigned', [
                    'assignment_id' => (int) $assignment['id'],
                    'assessment_id' => (int) $assignment['assessment_id'],
                    'job_id' => $assignment['job_id'] === null ? null : (int) $assignment['job_id'],
                ]);
            }

            $this->auditRecord($context, 'assessments.assign', 'assessment_assignment', (int) $assignment['id'], null, $assignment);

            return $this->assignmentPayload($assignment);
        });
    }

    public function start(int $assignmentId, array $user): array
    {
        $assignment = $this->requireAssignment($assignmentId);
        $this->authorizeCandidateAssignment($assignment, $user);

        if (! in_array($assignment['status'], ['assigned', 'in_progress'], true)) {
            throw new HttpException('This assessment cannot be started.', 422);
        }

        if ($assignment['due_date'] !== null && strtotime((string) $assignment['due_date'] . ' 23:59:59') < time()) {
            $this->assignments->updateStatus($assignmentId, 'expired');
            throw new HttpException('This assessment assignment has expired.', 422);
        }

        return $this->assignmentPayload($this->assignments->start($assignmentId));
    }

    public function submit(int $assignmentId, array $data, array $user, array $context): array
    {
        $assignment = $this->requireAssignment($assignmentId);
        $this->authorizeCandidateAssignment($assignment, $user);

        if ($assignment['status'] === 'submitted' || $assignment['submitted_at'] !== null) {
            throw new HttpException('Submitted assessments cannot be modified.', 422);
        }

        if ($assignment['status'] !== 'in_progress') {
            throw new HttpException('Start the assessment before submitting answers.', 422);
        }

        $assessment = $this->requireAssessment((int) $assignment['assessment_id']);
        $this->enforceTimer($assignment, $assessment);

        return Database::transaction(function () use ($assignment, $assessment, $data, $user, $context): array {
            $summary = $this->saveAnswers($assignment, $data['answers']);
            $status = $summary['requires_manual'] ? 'manual_review' : ($summary['percentage'] >= (float) $assessment['pass_mark'] ? 'passed' : 'failed');
            $result = $this->results->upsert((int) $assignment['id'], $summary['total'], $summary['percentage'], $status, $summary['requires_manual'] ? null : (int) $user['id']);
            $assignment = $this->assignments->submit((int) $assignment['id'], $summary['requires_manual'] ? 'submitted' : 'graded');

            if (! $summary['requires_manual'] && $assignment['application_id'] !== null) {
                $this->advanceApplication($this->applications->findById((int) $assignment['application_id']), 'assessment_completed', (int) $user['id'], 'Assessment completed.');
            }

            $this->auditRecord($context, 'assessments.submit', 'assessment_assignment', (int) $assignment['id'], null, ['result' => $result]);

            return $this->assignmentPayload($assignment);
        });
    }

    public function grade(int $assignmentId, array $data, array $user, array $context): array
    {
        $assignment = $this->requireAssignment($assignmentId);

        if ($assignment['submitted_at'] === null) {
            throw new HttpException('Assessment must be submitted before grading.', 422);
        }

        return Database::transaction(function () use ($assignment, $data, $user, $context): array {
            foreach ($data['scores'] as $questionId => $score) {
                $question = $this->questions->findById((int) $questionId);

                if ($question === null || (int) $question['assessment_id'] !== (int) $assignment['assessment_id']) {
                    throw new HttpException('Manual score references an invalid question.', 422);
                }

                if ((float) $score < 0 || (float) $score > (float) $question['score']) {
                    throw new HttpException('Manual score is outside the allowed question score.', 422, [
                        'scores' => ['Manual score cannot be negative or exceed the question score.'],
                    ]);
                }

                $this->answers->upsert((int) $assignment['id'], (int) $questionId, $this->answerValue($assignment, (int) $questionId), (float) $score);
            }

            $summary = $this->scoreSummary((int) $assignment['id'], (int) $assignment['assessment_id']);
            $assessment = $this->requireAssessment((int) $assignment['assessment_id']);
            $status = $summary['percentage'] >= (float) $assessment['pass_mark'] ? 'passed' : 'failed';
            $result = $this->results->upsert((int) $assignment['id'], $summary['total'], $summary['percentage'], $status, (int) $user['id']);
            $assignment = $this->assignments->updateStatus((int) $assignment['id'], 'graded');

            if ($assignment['application_id'] !== null) {
                $this->advanceApplication($this->applications->findById((int) $assignment['application_id']), 'assessment_completed', (int) $user['id'], 'Assessment graded.');
            }

            $this->auditRecord($context, 'assessments.grade', 'assessment_result', (int) $result['id'], null, $result);

            return $this->assignmentPayload($assignment);
        });
    }

    private function saveAnswers(array $assignment, array $answers): array
    {
        $questions = $this->questions->forAssessment((int) $assignment['assessment_id']);
        $questionMap = [];
        $requiresManual = false;

        foreach ($questions as $question) {
            $questionMap[(int) $question['id']] = $question;
        }

        foreach ($answers as $answer) {
            $questionId = (int) ($answer['question_id'] ?? 0);

            if (! isset($questionMap[$questionId])) {
                throw new HttpException('Submitted answer references an invalid question.', 422);
            }

            $question = $questionMap[$questionId];
            $value = $answer['answer'] ?? null;
            $score = null;

            if ($question['question_type'] === 'multiple_choice') {
                $score = $this->answersMatch($value, json_decode((string) $question['correct_answer_json'], true)) ? (float) $question['score'] : 0.0;
            } else {
                $requiresManual = true;
            }

            $this->answers->upsert((int) $assignment['id'], $questionId, $value, $score);
        }

        return $this->scoreSummary((int) $assignment['id'], (int) $assignment['assessment_id']) + ['requires_manual' => $requiresManual];
    }

    private function scoreSummary(int $assignmentId, int $assessmentId): array
    {
        $questions = $this->questions->forAssessment($assessmentId);
        $answers = $this->answers->forAssignment($assignmentId);
        $max = array_sum(array_map(fn (array $question): float => (float) $question['score'], $questions));
        $total = array_sum(array_map(fn (array $answer): float => (float) ($answer['score_awarded'] ?? 0), $answers));

        return ['total' => $total, 'percentage' => $max > 0 ? round(($total / $max) * 100, 2) : 0.0];
    }

    private function answerValue(array $assignment, int $questionId): mixed
    {
        foreach ($this->answers->forAssignment((int) $assignment['id']) as $answer) {
            if ((int) $answer['question_id'] === $questionId) {
                return json_decode((string) $answer['answer_json'], true);
            }
        }

        return null;
    }

    private function assignmentPayload(array $assignment): array
    {
        return [
            'assignment' => $assignment,
            'result' => $this->results->findByAssignment((int) $assignment['id']),
            'answers' => $this->answers->forAssignment((int) $assignment['id']),
        ];
    }

    private function enforceTimer(array $assignment, array $assessment): void
    {
        if ($assignment['started_at'] === null) {
            throw new HttpException('Assessment has not been started.', 422);
        }

        $deadline = strtotime((string) $assignment['started_at']) + ((int) $assessment['duration_minutes'] * 60);

        if ($deadline < time()) {
            $this->assignments->updateStatus((int) $assignment['id'], 'expired');
            throw new HttpException('Assessment time has expired.', 422);
        }
    }

    private function authorizeCandidateAssignment(array $assignment, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array('hr_officer', $roles, true)) {
            return;
        }

        $profile = $this->profiles->findByUserId((int) $user['id']);

        if ($profile !== null && (int) $profile['id'] === (int) $assignment['job_seeker_id']) {
            return;
        }

        throw new HttpException('You are not allowed to access this assessment assignment.', 403);
    }

    private function advanceApplication(?array $application, string $stage, int $actorId, string $note): void
    {
        if ($application === null || ! in_array($stage, ApplicationService::TRANSITIONS[$application['current_stage']] ?? [], true)) {
            return;
        }

        $this->applications->updateStage((int) $application['id'], $stage);
        $this->stageLogs->create((int) $application['id'], $application['current_stage'], $stage, $actorId, $note);
    }

    private function answersMatch(mixed $answer, mixed $correct): bool
    {
        return json_encode($answer) === json_encode($correct);
    }

    private function requireAssessment(int $id): array
    {
        $assessment = $this->assessments->findById($id);

        if ($assessment === null) {
            throw new HttpException('Assessment not found.', 404);
        }

        return $assessment;
    }

    private function requireAssignment(int $id): array
    {
        $assignment = $this->assignments->findById($id);

        if ($assignment === null) {
            throw new HttpException('Assessment assignment not found.', 404);
        }

        return $assignment;
    }

    private function auditRecord(array $context, string $action, string $entityType, int $entityId, ?array $old, array $new): void
    {
        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'module' => 'assessments',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }
}
