<?php

declare(strict_types=1);

namespace App\Modules\Matching\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\Matching\Repositories\CandidateRepository;
use App\Modules\Matching\Repositories\CandidateUnlockRepository;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;
use App\Modules\Wallet\Services\WalletService;

class CandidateUnlockService
{
    public function __construct(
        private readonly CandidateUnlockRepository $unlocks,
        private readonly CandidateRepository $candidates,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly WalletService $wallets,
        private readonly AuditLogService $audit
    ) {
    }

    public function unlock(int $profileId, ?int $jobId, array $user, array $context): array
    {
        $recruiter = $this->recruiters->findByUserId((int) $user['id']);

        if ($recruiter === null || $recruiter['verification_status'] !== 'verified') {
            throw new HttpException('Verified recruiter profile is required to unlock candidates.', 422);
        }

        if ($this->candidates->findProfile($profileId) === null) {
            throw new HttpException('Candidate profile not found.', 404);
        }

        $active = $this->unlocks->active((int) $recruiter['id'], $profileId, $jobId);

        if ($active !== null) {
            return $active;
        }

        $fee = (float) config('wallet.candidate_unlock_fee', 5000);
        $days = (int) config('wallet.candidate_unlock_days', 30);

        return Database::transaction(function () use ($profileId, $jobId, $user, $context, $recruiter, $fee, $days): array {
            $transaction = $this->wallets->debit(
                (int) $user['id'],
                $fee,
                'candidate_unlock_fee',
                'UNLOCK-' . strtoupper(bin2hex(random_bytes(8))),
                'Candidate profile unlock fee.',
                ['job_seeker_id' => $profileId, 'job_id' => $jobId],
                $context
            );
            $unlock = $this->unlocks->create(
                (int) $recruiter['id'],
                $profileId,
                $jobId,
                (int) $transaction['id'],
                (int) $user['id'],
                date('Y-m-d H:i:s', strtotime("+{$days} days"))
            );
            $this->audit->record([
                'actor_id' => (int) $user['id'],
                'action' => 'candidates.unlock',
                'module' => 'matching',
                'entity_type' => 'candidate_unlock',
                'entity_id' => (int) $unlock['id'],
                'new_values' => ['job_seeker_id' => $profileId, 'fee' => $fee],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            return $unlock;
        });
    }
}
