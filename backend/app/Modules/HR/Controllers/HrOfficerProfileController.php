<?php

declare(strict_types=1);

namespace App\Modules\HR\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\HR\Requests\UpdateHrOfficerProfileRequest;
use App\Modules\HR\Resources\HrOfficerProfileResource;
use App\Modules\HR\Services\HrOfficerProfileService;

class HrOfficerProfileController extends Controller
{
    public function __construct(
        private readonly HrOfficerProfileService $profiles,
        private readonly UpdateHrOfficerProfileRequest $updateRequest
    ) {
    }

    public function mine(Request $request): void
    {
        $this->success(
            HrOfficerProfileResource::make($this->profiles->mine((int) $request->user()['id'])),
            'HR officer profile retrieved successfully.'
        );
    }

    public function update(Request $request): void
    {
        $profile = $this->profiles->update((int) $request->user()['id'], $this->updateRequest->validate($request), [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(HrOfficerProfileResource::make($profile), 'HR officer profile updated successfully.');
    }
}
