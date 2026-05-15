<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Applications\Repositories\ApplicationRepository;
use App\Modules\Applications\Repositories\ApplicationStageLogRepository;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\JobSeekers\Repositories\JobSeekerProfileRepository;
use App\Modules\Jobs\Repositories\JobRepository;
use App\Modules\Matching\Services\CandidateMatchingService;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;

class ApplicationService
{
    public const STAGES = [
        'applied',
        'matched',
        'screening',
        'assessment_invited',
        'assessment_completed',
        'shortlisted',
        'interview_scheduled',
        'interview_completed',
        'offer_pending',
        'offer_accepted',
        'placed',
        'rejected',
        'withdrawn',
    ];

    public const TRANSITIONS = [
        'applied' => ['screening', 'assessment_invited', 'shortlisted', 'rejected', 'withdrawn'],
        'matched' => ['screening', 'shortlisted', 'rejected', 'withdrawn'],
        'screening' => ['assessment_invited', 'shortlisted', 'rejected', 'withdrawn'],
        'assessment_invited' => ['assessment_completed', 'rejected', 'withdrawn'],
        'assessment_completed' => ['shortlisted', 'interview_scheduled', 'rejected', 'withdrawn'],
        'shortlisted' => ['interview_scheduled', 'offer_pending', 'rejected', 'withdrawn'],
        'interview_scheduled' => ['interview_completed', 'rejected', 'withdrawn'],
        'interview_completed' => ['offer_pending', 'rejected', 'withdrawn'],
        'offer_pending' => ['offer_accepted', 'rejected', 'withdrawn'],
        'offer_accepted' => ['placed', 'withdrawn'],
        'placed' => [],
        'rejected' => [],
        'withdrawn' => [],
    ];

    public function __construct(
        private readonly ApplicationRepository $applications,
        private readonly ApplicationStageLogRepository $logs,
        private readonly JobRepository $jobs,
        private readonly JobSeekerProfileRepository $profiles,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly CandidateMatchingService $matching,
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

        return $this->applications->list($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function apply(int $jobId, array $data, array $user, array $context): array
    {
        $profile = $this->profiles->findByUserId((int) $user['id']);

        if ($profile === null) {
            throw new HttpException('Complete job seeker profile before applying.', 422);
        }

        $job = $this->jobs->findById($jobId);

        if ($job === null) {
            throw new HttpException('Job not found.', 404);
        }

        if (! in_array($job['status'], ['published', 'open', 'assigned'], true)) {
            throw new HttpException('This job is not accepting applications.', 422);
        }

        if ($this->applications->findExisting($jobId, (int) $profile['id']) !== null) {
            throw new HttpException('You have already applied for this job.', 409);
        }

        return Database::transaction(function () use ($job, $profile, $data, $user, $context): array {
            $score = $this->matching->score($job, (int) $profile['id'])['score'];
            $application = $this->applications->create([
                'job_id' => (int) $job['id'],
                'job_seeker_id' => (int) $profile['id'],
                'applied_by' => (int) $user['id'],
                'cover_letter' => $data['cover_letter'] ?? null,
                'match_score' => $score,
            ]);
            $this->logs->create((int) $application['id'], null, 'applied', (int) $user['id'], 'Application submitted.');
            $this->notifyApplicationStakeholders($application, $job, 'application_submitted', 'Application submitted', 'A candidate submitted a job application.');
            $this->auditApplication($context, 'applications.create', null, $application);

            return $this->withLogs($application);
        });
    }

    public function show(int $id, array $user): array
    {
        $application = $this->requireApplication($id);
        $this->authorizeView($application, $user);

        return $this->withLogs($application);
    }

    public function moveStage(int $id, string $stage, ?string $note, array $user, array $context): array
    {
        $old = $this->requireApplication($id);
        $this->authorizeManage($old, $user);

        if (! in_array($stage, self::STAGES, true)) {
            throw new HttpException('Application stage is not supported.', 422, [
                'stage' => ['Application stage is not supported.'],
            ]);
        }

        if (! in_array($stage, self::TRANSITIONS[$old['current_stage']] ?? [], true)) {
            throw new HttpException('Invalid application stage transition.', 422, [
                'stage' => ["Cannot move application from {$old['current_stage']} to {$stage}."],
            ]);
        }

        $status = in_array($stage, ['rejected', 'withdrawn'], true) ? $stage : 'active';

        return Database::transaction(function () use ($id, $stage, $status, $note, $user, $context, $old): array {
            $application = $this->applications->updateStage($id, $stage, $status);
            $this->logs->create($id, $old['current_stage'], $stage, (int) $user['id'], $note);

            if ($stage === 'shortlisted') {
                $profile = $this->profiles->findById((int) $application['job_seeker_id']);

                if ($profile !== null) {
                    $this->notifications->notify((int) $profile['user_id'], 'Candidate shortlisted', 'Your application has been shortlisted.', 'candidate_shortlisted', [
                        'application_id' => (int) $application['id'],
                        'job_id' => (int) $application['job_id'],
                    ]);
                }
            }

            $this->auditApplication($context, 'applications.move_stage', $old, $application);

            return $this->withLogs($application);
        });
    }

    public function shortlist(int $id, array $user, array $context): array
    {
        return $this->moveStage($id, 'shortlisted', 'Candidate shortlisted.', $user, $context);
    }

    public function reject(int $id, array $user, array $context): array
    {
        return $this->moveStage($id, 'rejected', 'Candidate rejected.', $user, $context);
    }

    public function withdraw(int $id, array $user, array $context): array
    {
        $application = $this->requireApplication($id);
        $profile = $this->profiles->findByUserId((int) $user['id']);

        if ($profile === null || (int) $profile['id'] !== (int) $application['job_seeker_id']) {
            throw new HttpException('Only the applicant can withdraw this application.', 403);
        }

        return $this->moveStage($id, 'withdrawn', 'Application withdrawn by candidate.', $user, $context);
    }

    private function authorizeView(array $application, array $user): void
    {
        if ($this->canManage($application, $user)) {
            return;
        }

        $profile = $this->profiles->findByUserId((int) $user['id']);

        if ($profile !== null && (int) $profile['id'] === (int) $application['job_seeker_id']) {
            return;
        }

        throw new HttpException('You are not allowed to view this application.', 403);
    }

    private function authorizeManage(array $application, array $user): void
    {
        if (! $this->canManage($application, $user)) {
            throw new HttpException('You are not allowed to manage this application.', 403);
        }
    }

    private function canManage(array $application, array $user): bool
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return true;
        }

        $job = $this->jobs->findById((int) $application['job_id']);

        if ($job === null) {
            return false;
        }

        if (in_array('recruiter', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);

            return $recruiter !== null && (int) $recruiter['id'] === (int) $job['recruiter_id'];
        }

        if (in_array('hr_officer', $roles, true) && (int) ($job['assigned_hr_officer_id'] ?? 0) === (int) $user['id']) {
            return true;
        }

        return in_array('relationship_officer', $roles, true)
            && (int) ($job['assigned_relationship_officer_id'] ?? 0) === (int) $user['id'];
    }

    private function requireApplication(int $id): array
    {
        $application = $this->applications->findById($id);

        if ($application === null) {
            throw new HttpException('Application not found.', 404);
        }

        return $application;
    }

    private function withLogs(array $application): array
    {
        return ['application' => $application, 'logs' => $this->logs->forApplication((int) $application['id'])];
    }

    private function auditApplication(array $context, string $action, ?array $old, array $new): void
    {
        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? null,
            'action' => $action,
            'module' => 'applications',
            'entity_type' => 'job_application',
            'entity_id' => (int) $new['id'],
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);
    }

    private function notifyApplicationStakeholders(array $application, array $job, string $type, string $title, string $body): void
    {
        $recipients = [];

        if (! empty($job['assigned_hr_officer_id'])) {
            $recipients[] = (int) $job['assigned_hr_officer_id'];
        }

        $recruiter = $this->recruiters->findById((int) $job['recruiter_id']);

        if ($recruiter !== null) {
            $recipients[] = (int) $recruiter['user_id'];
        }

        foreach (array_unique($recipients) as $userId) {
            $this->notifications->notify($userId, $title, $body, $type, [
                'application_id' => (int) $application['id'],
                'job_id' => (int) $application['job_id'],
            ]);
        }
    }
}
