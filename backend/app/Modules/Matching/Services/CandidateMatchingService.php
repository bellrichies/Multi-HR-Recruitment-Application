<?php

declare(strict_types=1);

namespace App\Modules\Matching\Services;

use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Jobs\Repositories\JobRepository;
use App\Modules\Jobs\Repositories\JobSkillRepository;
use App\Modules\Matching\Repositories\CandidateMatchRepository;
use App\Modules\Matching\Repositories\CandidateRepository;
use App\Modules\Matching\Repositories\CandidateUnlockRepository;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;

class CandidateMatchingService
{
    public function __construct(
        private readonly CandidateRepository $candidates,
        private readonly CandidateMatchRepository $matches,
        private readonly CandidateUnlockRepository $unlocks,
        private readonly JobRepository $jobs,
        private readonly JobSkillRepository $jobSkills,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly AuditLogService $audit
    ) {
    }

    public function discover(array $filters): array
    {
        return $this->candidates->discover($filters, (int) ($filters['page'] ?? 1), (int) ($filters['per_page'] ?? 20));
    }

    public function canViewFullProfile(int $profileId, array $user, ?int $jobId = null): bool
    {
        return $this->canViewFull($profileId, $user, $jobId);
    }

    public function summary(int $profileId): array
    {
        $profile = $this->requireProfile($profileId);

        return ['profile' => $profile, 'match' => null];
    }

    public function fullProfile(int $profileId, array $user, ?int $jobId = null): array
    {
        $profile = $this->requireProfile($profileId);

        if (! $this->canViewFull($profileId, $user, $jobId)) {
            throw new HttpException('Candidate full profile requires active unlock access.', 403);
        }

        return [
            'profile' => $profile,
            'skills' => $this->candidates->skills($profileId),
            'match' => $jobId === null ? null : $this->matches->find($jobId, $profileId),
        ];
    }

    public function matchCandidates(int $jobId, array $user, ?int $profileId = null, array $context = []): array
    {
        $job = $this->jobs->findById($jobId);

        if ($job === null) {
            throw new HttpException('Job not found.', 404);
        }

        $profiles = $profileId !== null
            ? [$this->requireProfile($profileId)]
            : $this->candidates->discover(['per_page' => 100], 1, 100)['data'];
        $results = [];

        foreach ($profiles as $profile) {
            $score = $this->score($job, (int) $profile['id']);
            $results[] = $this->matches->upsert(
                $jobId,
                (int) $profile['id'],
                (int) $user['id'],
                $score['score'],
                $score['reason'],
                'matched'
            );
        }

        $this->audit->record([
            'actor_id' => $context['actor_id'] ?? $user['id'] ?? null,
            'action' => 'candidates.match',
            'module' => 'matching',
            'entity_type' => 'job',
            'entity_id' => $jobId,
            'new_values' => ['matched_count' => count($results)],
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
        ]);

        return $results;
    }

    public function matchesForJob(int $jobId, array $user): array
    {
        $job = $this->jobs->findById($jobId);

        if ($job === null) {
            throw new HttpException('Job not found.', 404);
        }

        $this->authorizeJobCandidateAccess($job, $user);

        return $this->matches->forJob($jobId);
    }

    public function score(array $job, int $profileId): array
    {
        $profile = $this->requireProfile($profileId);
        $candidateSkills = array_map('strtolower', $this->candidates->skills($profileId));
        $requiredSkills = array_map(
            fn (array $skill): string => strtolower((string) $skill['skill_name']),
            $this->jobSkills->forJob((int) $job['id'])
        );
        $score = 0;
        $reasons = [];

        if ($requiredSkills !== []) {
            $matched = array_intersect($requiredSkills, $candidateSkills);
            $skillScore = (count($matched) / count($requiredSkills)) * 40;
            $score += $skillScore;
            $reasons[] = count($matched) . ' of ' . count($requiredSkills) . ' required skills matched';
        }

        if (! empty($job['location']) && ! empty($profile['location']) && strtolower($job['location']) === strtolower($profile['location'])) {
            $score += 20;
            $reasons[] = 'Location matched';
        }

        if (! empty($profile['years_of_experience'])) {
            $score += min(15, (float) $profile['years_of_experience'] * 3);
            $reasons[] = 'Experience considered';
        }

        if (! empty($profile['availability_status'])) {
            $score += 10;
            $reasons[] = 'Availability provided';
        }

        if ((int) $profile['profile_completion_percentage'] >= 80) {
            $score += 15;
            $reasons[] = 'Profile completion is strong';
        }

        return ['score' => round(min(100, $score), 2), 'reason' => implode('; ', $reasons)];
    }

    private function canViewFull(int $profileId, array $user, ?int $jobId): bool
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true) || in_array('hr_officer', $roles, true)) {
            return true;
        }

        if (in_array('job_seeker', $roles, true)) {
            return (int) ($user['id'] ?? 0) === (int) $this->requireProfile($profileId)['user_id'];
        }

        if (! in_array('recruiter', $roles, true)) {
            return false;
        }

        $recruiter = $this->recruiters->findByUserId((int) $user['id']);

        if ($recruiter === null) {
            return false;
        }

        return $this->unlocks->active((int) $recruiter['id'], $profileId, $jobId) !== null;
    }

    private function authorizeJobCandidateAccess(array $job, array $user): void
    {
        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        if (in_array('hr_officer', $roles, true) && (int) ($job['assigned_hr_officer_id'] ?? 0) === (int) $user['id']) {
            return;
        }

        if (in_array('relationship_officer', $roles, true) && (int) ($job['assigned_relationship_officer_id'] ?? 0) === (int) $user['id']) {
            return;
        }

        if (in_array('recruiter', $roles, true)) {
            $recruiter = $this->recruiters->findByUserId((int) $user['id']);

            if ($recruiter !== null && (int) $recruiter['id'] === (int) $job['recruiter_id']) {
                return;
            }
        }

        throw new HttpException('You are not allowed to access candidates for this job.', 403);
    }

    private function requireProfile(int $profileId): array
    {
        $profile = $this->candidates->findProfile($profileId);

        if ($profile === null) {
            throw new HttpException('Candidate profile not found.', 404);
        }

        return $profile;
    }
}
