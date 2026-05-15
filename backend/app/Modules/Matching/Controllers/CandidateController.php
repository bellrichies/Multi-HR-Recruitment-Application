<?php

declare(strict_types=1);

namespace App\Modules\Matching\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Matching\Requests\MatchCandidateRequest;
use App\Modules\Matching\Resources\CandidateMatchResource;
use App\Modules\Matching\Resources\CandidateResource;
use App\Modules\Matching\Services\CandidateMatchingService;

class CandidateController extends Controller
{
    public function __construct(
        private readonly CandidateMatchingService $matching,
        private readonly MatchCandidateRequest $matchRequest
    ) {
    }

    public function discover(Request $request): void
    {
        $result = $this->matching->discover($request->query());

        $this->success(
            array_map(fn (array $profile): array => CandidateResource::summary($profile), $result['data']),
            'Candidates retrieved successfully.',
            $result['meta']
        );
    }

    public function summary(Request $request, string $id): void
    {
        $result = $this->matching->summary((int) $id);
        $jobId = $request->query('job_id') === null ? null : (int) $request->query('job_id');
        $summary = CandidateResource::summary($result['profile'], $result['match']);
        $summary['full_profile_unlocked'] = $this->matching->canViewFullProfile((int) $id, $request->user(), $jobId);

        $this->success($summary, 'Candidate summary retrieved successfully.');
    }

    public function fullProfile(Request $request, string $id): void
    {
        $jobId = $request->query('job_id') === null ? null : (int) $request->query('job_id');
        $result = $this->matching->fullProfile((int) $id, $request->user(), $jobId);

        $this->success(
            CandidateResource::full($result['profile'], $result['skills'], $result['match']),
            'Candidate full profile retrieved successfully.'
        );
    }

    public function matchCandidates(Request $request, string $jobId): void
    {
        $data = $this->matchRequest->validate($request);
        $matches = $this->matching->matchCandidates((int) $jobId, $request->user(), isset($data['job_seeker_id']) ? (int) $data['job_seeker_id'] : null, [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(CandidateMatchResource::collection($matches), 'Candidates matched successfully.');
    }

    public function jobMatches(Request $request, string $jobId): void
    {
        $matches = $this->matching->matchesForJob((int) $jobId, $request->user());

        $this->success(CandidateMatchResource::collection($matches), 'Candidate matches retrieved successfully.');
    }
}
