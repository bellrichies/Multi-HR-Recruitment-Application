<?php

declare(strict_types=1);

namespace App\Modules\Matching\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Matching\Requests\UnlockCandidateRequest;
use App\Modules\Matching\Resources\CandidateUnlockResource;
use App\Modules\Matching\Services\CandidateUnlockService;

class CandidateUnlockController extends Controller
{
    public function __construct(
        private readonly CandidateUnlockService $unlocks,
        private readonly UnlockCandidateRequest $unlockRequest
    ) {
    }

    public function store(Request $request, string $id): void
    {
        $data = $this->unlockRequest->validate($request);
        $unlock = $this->unlocks->unlock((int) $id, isset($data['job_id']) ? (int) $data['job_id'] : null, $request->user(), [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(CandidateUnlockResource::make($unlock), 'Candidate unlocked successfully.', [], 201);
    }
}
