<?php

declare(strict_types=1);

namespace App\Modules\Interviews\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Applications\Repositories\ApplicationRepository;
use App\Modules\Applications\Repositories\ApplicationStageLogRepository;
use App\Modules\Applications\Services\ApplicationService;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Interviews\Repositories\InterviewFeedbackRepository;
use App\Modules\Interviews\Repositories\InterviewRepository;
use App\Modules\JobSeekers\Repositories\JobSeekerProfileRepository;
use App\Modules\Jobs\Repositories\JobRepository;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;

class InterviewService
{
    public function __construct(
        private readonly InterviewRepository $interviews,
        private readonly InterviewFeedbackRepository $feedback,
        private readonly ApplicationRepository $applications,
        private readonly ApplicationStageLogRepository $stageLogs,
        private readonly JobRepository $jobs,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly JobSeekerProfileRepository $profiles,
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
        }

        if (in_array('recruiter', $roles, true) && ! in_array('super_admin', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);
            $filters['recruiter_id'] = $recruiter['id'] ?? 0;
        }

        return $this->interviews->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function show(int $id, array $user): array
    {
        $interview = $this->requireInterview($id);
        $this->authorizeView($interview, $user);

        return ['interview' => $interview, 'feedback' => $this->feedback->forInterview($id)];
    }

    public function schedule(array $data, array $user, array $context): array
    {
        $application = $this->requireApplication((int) $data['application_id']);
        $job = $this->requireJob((int) $application['job_id']);
        $this->authorizeSchedule($job, $user);

        return Database::transaction(function () use ($data, $user, $context, $application, $job): array {
            $interview = $this->interviews->create($data + [
                'job_id' => (int) $application['job_id'],
                'job_seeker_id' => (int) $application['job_seeker_id'],
                'recruiter_id' => (int) $job['recruiter_id'],
                'scheduled_by' => (int) $user['id'],
            ]);
            $this->advanceApplication($application, 'interview_scheduled', (int) $user['id'], 'Interview scheduled.');
            $this->notifyInterviewParticipants($interview, 'Interview scheduled', 'An interview has been scheduled.', 'interview_scheduled');
            $this->auditRecord($context, 'interviews.schedule', (int) $interview['id'], null, $interview + ['notification_event' => 'interview_scheduled']);

            return ['interview' => $interview, 'feedback' => []];
        });
    }

    public function update(int $id, array $data, array $user, array $context): array
    {
        return $this->reschedule($id, $data, $user, $context);
    }

    public function reschedule(int $id, array $data, array $user, array $context): array
    {
        $old = $this->requireInterview($id);
        $this->authorizeSchedule($this->requireJob((int) $old['job_id']), $user);

        if (in_array($old['status'], ['completed', 'cancelled'], true)) {
            throw new HttpException('Completed or cancelled interviews cannot be rescheduled.', 422);
        }

        return Database::transaction(function () use ($id, $data, $context, $old): array {
            $interview = $this->interviews->reschedule($id, $data);
            $this->notifyInterviewParticipants($interview, 'Interview rescheduled', 'An interview has been rescheduled.', 'interview_scheduled');
            $this->auditRecord($context, 'interviews.reschedule', $id, $old, $interview + ['notification_event' => 'interview_rescheduled']);

            return ['interview' => $interview, 'feedback' => $this->feedback->forInterview($id)];
        });
    }

    public function cancel(int $id, array $user, array $context): array
    {
        $old = $this->requireInterview($id);
        $this->authorizeSchedule($this->requireJob((int) $old['job_id']), $user);

        return Database::transaction(function () use ($id, $context, $old): array {
            $interview = $this->interviews->updateStatus($id, 'cancelled');
            $this->notifyInterviewParticipants($interview, 'Interview cancelled', 'An interview has been cancelled.', 'interview_scheduled');
            $this->auditRecord($context, 'interviews.cancel', $id, $old, $interview + ['notification_event' => 'interview_cancelled']);

            return ['interview' => $interview, 'feedback' => $this->feedback->forInterview($id)];
        });
    }

    public function submitFeedback(int $id, array $data, array $user, array $context): array
    {
        $interview = $this->requireInterview($id);
        $this->authorizeFeedback($interview, $user);

        if ($interview['status'] === 'cancelled') {
            throw new HttpException('Feedback cannot be submitted for a cancelled interview.', 422);
        }

        return Database::transaction(function () use ($id, $data, $user, $context, $interview): array {
            $feedback = $this->feedback->create($data + ['interview_id' => $id, 'submitted_by' => (int) $user['id']]);
            $updated = $this->interviews->updateStatus($id, 'completed');
            $application = $this->applications->findById((int) $interview['application_id']);
            $stage = $data['recommendation'] === 'reject' ? 'rejected' : 'interview_completed';

            if ($application !== null) {
                $this->advanceApplication($application, $stage, (int) $user['id'], 'Interview feedback submitted.');
            }

            $this->auditRecord($context, 'interviews.feedback', (int) $feedback['id'], null, $feedback);

            return ['interview' => $updated, 'feedback' => $this->feedback->forInterview($id)];
        });
    }

    private function authorizeSchedule(array $job, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        if (in_array('hr_officer', $roles, true) && (int) ($job['assigned_hr_officer_id'] ?? 0) === (int) $user['id']) {
            return;
        }

        throw new HttpException('Only the assigned HR Officer or Super Admin can schedule this interview.', 403);
    }

    private function authorizeFeedback(array $interview, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array('hr_officer', $roles, true)) {
            return;
        }

        if (in_array('recruiter', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);

            if ($recruiter !== null && (int) $recruiter['id'] === (int) $interview['recruiter_id']) {
                return;
            }
        }

        throw new HttpException('You are not allowed to submit feedback for this interview.', 403);
    }

    private function authorizeView(array $interview, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array('hr_officer', $roles, true)) {
            return;
        }

        if (in_array('job_seeker', $roles, true)) {
            $profile = $this->profiles->findByUserId((int) $user['id']);

            if ($profile !== null && (int) $profile['id'] === (int) $interview['job_seeker_id']) {
                return;
            }
        }

        if (in_array('recruiter', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);

            if ($recruiter !== null && (int) $recruiter['id'] === (int) $interview['recruiter_id']) {
                return;
            }
        }

        throw new HttpException('You are not allowed to view this interview.', 403);
    }

    private function advanceApplication(array $application, string $stage, int $actorId, string $note): void
    {
        if (! in_array($stage, ApplicationService::TRANSITIONS[$application['current_stage']] ?? [], true)) {
            return;
        }

        $this->applications->updateStage((int) $application['id'], $stage, $stage === 'rejected' ? 'rejected' : 'active');
        $this->stageLogs->create((int) $application['id'], $application['current_stage'], $stage, $actorId, $note);
    }

    private function requireInterview(int $id): array
    {
        $interview = $this->interviews->findById($id);

        if ($interview === null) {
            throw new HttpException('Interview not found.', 404);
        }

        return $interview;
    }

    private function requireApplication(int $id): array
    {
        $application = $this->applications->findById($id);

        if ($application === null) {
            throw new HttpException('Application not found.', 404);
        }

        return $application;
    }

    private function requireJob(int $id): array
    {
        $job = $this->jobs->findById($id);

        if ($job === null) {
            throw new HttpException('Job not found.', 404);
        }

        return $job;
    }

    private function auditRecord(array $context, string $action, int $entityId, ?array $old, array $new): void
    {
        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'module' => 'interviews',
            'entity_type' => 'interview',
            'entity_id' => $entityId,
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }

    private function notifyInterviewParticipants(array $interview, string $title, string $body, string $type): void
    {
        $profile = $this->profiles->findById((int) $interview['job_seeker_id']);
        $recruiter = $this->recruiters->findById((int) $interview['recruiter_id']);
        $recipients = [];

        if ($profile !== null) {
            $recipients[] = (int) $profile['user_id'];
        }

        if ($recruiter !== null) {
            $recipients[] = (int) $recruiter['user_id'];
        }

        foreach (array_unique($recipients) as $userId) {
            $this->notifications->notify($userId, $title, $body, $type, [
                'interview_id' => (int) $interview['id'],
                'application_id' => (int) $interview['application_id'],
                'job_id' => (int) $interview['job_id'],
            ]);
        }
    }
}
