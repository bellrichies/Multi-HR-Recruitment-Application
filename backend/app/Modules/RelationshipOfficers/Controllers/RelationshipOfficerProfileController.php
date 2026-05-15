<?php

declare(strict_types=1);

namespace App\Modules\RelationshipOfficers\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\RelationshipOfficers\Requests\UpdateRelationshipOfficerProfileRequest;
use App\Modules\RelationshipOfficers\Resources\RelationshipOfficerProfileResource;
use App\Modules\RelationshipOfficers\Services\RelationshipOfficerProfileService;

class RelationshipOfficerProfileController extends Controller
{
    public function __construct(
        private readonly RelationshipOfficerProfileService $profiles,
        private readonly UpdateRelationshipOfficerProfileRequest $updateRequest
    ) {
    }

    public function mine(Request $request): void
    {
        $this->success(
            RelationshipOfficerProfileResource::make($this->profiles->mine((int) $request->user()['id'])),
            'Relationship officer profile retrieved successfully.'
        );
    }

    public function update(Request $request): void
    {
        $profile = $this->profiles->update((int) $request->user()['id'], $this->updateRequest->validate($request), [
            'actor_id' => $request->user()['id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $this->success(
            RelationshipOfficerProfileResource::make($profile),
            'Relationship officer profile updated successfully.'
        );
    }
}
