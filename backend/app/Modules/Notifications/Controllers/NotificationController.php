<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Notifications\Requests\UpdateNotificationPreferenceRequest;
use App\Modules\Notifications\Resources\NotificationPreferenceResource;
use App\Modules\Notifications\Resources\NotificationResource;
use App\Modules\Notifications\Services\NotificationService;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notifications,
        private readonly UpdateNotificationPreferenceRequest $preferenceRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->notifications->list($request->user(), (int) $request->query('page', 1), (int) $request->query('per_page', 20));

        $this->success(NotificationResource::collection($result['data']), 'Notifications retrieved successfully.', $result['meta']);
    }

    public function unreadCount(Request $request): void
    {
        $this->success(['unread_count' => $this->notifications->unreadCount($request->user())], 'Unread notification count retrieved successfully.');
    }

    public function markRead(Request $request, string $id): void
    {
        $notification = $this->notifications->markRead((int) $id, $request->user());

        $this->success(NotificationResource::make($notification), 'Notification marked as read.');
    }

    public function markAllRead(Request $request): void
    {
        $this->notifications->markAllRead($request->user());

        $this->success([], 'Notifications marked as read.');
    }

    public function preferences(Request $request): void
    {
        $this->success(NotificationPreferenceResource::make($this->notifications->preferences($request->user())), 'Notification preferences retrieved successfully.');
    }

    public function updatePreferences(Request $request): void
    {
        $preference = $this->notifications->updatePreferences($request->user(), $this->preferenceRequest->validate($request));

        $this->success(NotificationPreferenceResource::make($preference), 'Notification preferences updated successfully.');
    }
}
