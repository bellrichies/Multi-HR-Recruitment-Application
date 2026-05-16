<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use App\Core\HttpException;
use App\Modules\Notifications\Repositories\EmailQueueRepository;
use App\Modules\Notifications\Repositories\NotificationPreferenceRepository;
use App\Modules\Notifications\Repositories\NotificationRepository;
use App\Modules\Users\Repositories\UserRepository;

class NotificationService
{
    public const EVENTS = [
        'job_match',
        'application_submitted',
        'candidate_shortlisted',
        'assessment_assigned',
        'interview_scheduled',
        'wallet_funded',
        'payment_failed',
        'placement_confirmed',
        'account_approved',
        'account_suspended',
        'message_received',
        'interview_request',
    ];

    public function __construct(
        private readonly NotificationRepository $notifications,
        private readonly NotificationPreferenceRepository $preferences,
        private readonly EmailQueueRepository $emailQueue,
        private readonly UserRepository $users,
        private readonly MailerService $mailer
    ) {
    }

    public function notify(int $userId, string $title, string $body, string $type, array $data = [], bool $queueEmail = true): ?array
    {
        $user = $this->users->findById($userId);

        if ($user === null) {
            return null;
        }

        $preference = $this->preferences->getOrCreate($userId);
        $notification = null;
        $eventPreferences = $preference['event_preferences_json'] === null
            ? []
            : (json_decode((string) $preference['event_preferences_json'], true) ?: []);

        if ((bool) $preference['in_app_enabled'] && $this->eventAllows($eventPreferences, $type, 'in_app')) {
            $notification = $this->notifications->create([
                'user_id' => $userId,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'channel' => 'in_app',
                'data' => $this->sanitizeData($data),
            ]);
        }

        if ($queueEmail && (bool) $preference['email_enabled'] && $this->eventAllows($eventPreferences, $type, 'email')) {
            $this->emailQueue->enqueue([
                'user_id' => $userId,
                'notification_id' => $notification['id'] ?? null,
                'recipient_email' => $user['email'],
                'subject' => $title,
                'body' => $body,
            ]);
        }

        return $notification;
    }

    public function list(array $user, int $page = 1, int $perPage = 20): array
    {
        return $this->notifications->listForUser((int) $user['id'], $page, $perPage);
    }

    public function show(int $id, array $user): array
    {
        $notification = $this->notifications->findForUser($id, (int) $user['id']);

        if ($notification === null) {
            throw new HttpException('Notification not found.', 404);
        }

        return $notification;
    }

    public function markRead(int $id, array $user): array
    {
        $notification = $this->notifications->markRead($id, (int) $user['id']);

        if ($notification === null) {
            throw new HttpException('Notification not found.', 404);
        }

        return $notification;
    }

    public function markUnread(int $id, array $user): array
    {
        $notification = $this->notifications->markUnread($id, (int) $user['id']);

        if ($notification === null) {
            throw new HttpException('Notification not found.', 404);
        }

        return $notification;
    }

    public function markAllRead(array $user): void
    {
        $this->notifications->markAllRead((int) $user['id']);
    }

    public function unreadCount(array $user): int
    {
        return $this->notifications->unreadCount((int) $user['id']);
    }

    public function preferences(array $user): array
    {
        return $this->preferences->getOrCreate((int) $user['id']);
    }

    public function updatePreferences(array $user, array $data): array
    {
        return $this->preferences->update(
            (int) $user['id'],
            (int) $data['in_app_enabled'] === 1,
            (int) $data['email_enabled'] === 1,
            $data['event_preferences']
        );
    }

    public function processEmailQueue(int $limit = 25): array
    {
        $processed = ['sent' => 0, 'failed' => 0];

        foreach ($this->emailQueue->pending($limit) as $job) {
            try {
                $this->mailer->send($job['recipient_email'], $job['subject'], $job['body']);
                $this->emailQueue->markSent((int) $job['id']);
                $processed['sent']++;
            } catch (\Throwable $exception) {
                $this->emailQueue->markFailed((int) $job['id'], $exception->getMessage());
                $processed['failed']++;
            }
        }

        return $processed;
    }

    private function sanitizeData(array $data): array
    {
        unset($data['cv_path'], $data['document_path'], $data['password'], $data['token']);

        return $data;
    }

    private function eventAllows(array $preferences, string $type, string $channel): bool
    {
        if (! array_key_exists($type, $preferences)) {
            return true;
        }

        if (is_bool($preferences[$type])) {
            return $preferences[$type];
        }

        return ! is_array($preferences[$type]) || ($preferences[$type][$channel] ?? true) !== false;
    }
}
