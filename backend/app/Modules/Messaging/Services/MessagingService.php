<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Services;

use App\Core\Database;
use App\Core\HttpException;
use App\Modules\Audit\Services\AuditLogService;
use App\Modules\JobSeekers\Repositories\JobSeekerProfileRepository;
use App\Modules\Jobs\Repositories\JobRepository;
use App\Modules\Messaging\Repositories\ConversationRepository;
use App\Modules\Messaging\Repositories\MessageRepository;
use App\Modules\Notifications\Services\NotificationService;
use App\Modules\Recruiters\Repositories\RecruiterProfileRepository;
use App\Modules\Users\Repositories\UserRepository;

class MessagingService
{
    public function __construct(
        private readonly ConversationRepository $conversations,
        private readonly MessageRepository $messages,
        private readonly UserRepository $users,
        private readonly JobRepository $jobs,
        private readonly RecruiterProfileRepository $recruiters,
        private readonly JobSeekerProfileRepository $profiles,
        private readonly NotificationService $notifications,
        private readonly AuditLogService $audit
    ) {
    }

    public function list(array $user, int $page = 1, int $perPage = 20): array
    {
        return $this->conversations->listForUser((int) $user['id'], $page, $perPage);
    }

    public function create(array $data, array $user, array $context): array
    {
        $participant = $this->users->findById((int) $data['participant_user_id']);

        if ($participant === null) {
            throw new HttpException('Conversation participant not found.', 404);
        }

        $this->authorizeConversation($user, (int) $participant['id'], $data);

        return Database::transaction(function () use ($data, $user, $participant, $context): array {
            $conversation = $this->conversations->create([
                'conversation_type' => $data['conversation_type'],
                'subject' => $data['subject'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'created_by' => (int) $user['id'],
            ]);
            $this->conversations->addParticipant((int) $conversation['id'], (int) $user['id']);
            $this->conversations->addParticipant((int) $conversation['id'], (int) $participant['id']);

            if (! empty($data['message_body'])) {
                $message = $this->messages->create([
                    'conversation_id' => (int) $conversation['id'],
                    'sender_id' => (int) $user['id'],
                    'message_body' => $data['message_body'],
                ]);
                $this->notifyParticipants($conversation, (int) $user['id'], 'New message', 'You have a new message.', 'message_received');
            }

            if ($data['conversation_type'] === 'interview_request') {
                $this->notifications->notify((int) $participant['id'], 'Interview request', 'A recruiter sent an interview request through HR.', 'interview_request', [
                    'conversation_id' => (int) $conversation['id'],
                    'job_id' => $conversation['job_id'],
                ]);
            }

            $this->audit->record([
                'actor_id' => $context['actor_id'] ?? $user['id'] ?? null,
                'action' => 'messages.conversation_create',
                'module' => 'messaging',
                'entity_type' => 'conversation',
                'entity_id' => (int) $conversation['id'],
                'new_values' => ['conversation_type' => $conversation['conversation_type'], 'participant_user_id' => (int) $participant['id']],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            return [
                'conversation' => $conversation,
                'participants' => $this->conversations->participants((int) $conversation['id']),
            ];
        });
    }

    public function messages(int $conversationId, array $user, int $page = 1, int $perPage = 50): array
    {
        $this->requireParticipant($conversationId, (int) $user['id']);
        $this->conversations->markRead($conversationId, (int) $user['id']);

        return $this->messages->forConversation($conversationId, $page, $perPage);
    }

    public function send(int $conversationId, array $data, array $user, array $context): array
    {
        $conversation = $this->requireConversation($conversationId);
        $this->requireParticipant($conversationId, (int) $user['id']);

        return Database::transaction(function () use ($conversation, $conversationId, $data, $user, $context): array {
            $message = $this->messages->create($data + [
                'conversation_id' => $conversationId,
                'sender_id' => (int) $user['id'],
            ]);
            $this->conversations->touch($conversationId);
            $this->notifyParticipants($conversation, (int) $user['id'], 'New message', 'You have a new message.', 'message_received');
            $this->audit->record([
                'actor_id' => $context['actor_id'] ?? $user['id'] ?? null,
                'action' => 'messages.send',
                'module' => 'messaging',
                'entity_type' => 'message',
                'entity_id' => (int) $message['id'],
                'new_values' => ['conversation_id' => $conversationId],
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ]);

            return $message;
        });
    }

    public function unreadCount(array $user): int
    {
        return $this->messages->unreadCount((int) $user['id']);
    }

    private function authorizeConversation(array $user, int $participantUserId, array $data): void
    {
        if ((int) $user['id'] === $participantUserId) {
            throw new HttpException('Conversation participant must be another user.', 422);
        }

        $roles = array_column($user['roles'] ?? [], 'slug');

        if (in_array('super_admin', $roles, true)) {
            return;
        }

        if (in_array('hr_officer', $roles, true)) {
            if ($this->hrCanMessageCandidate((int) $user['id'], $participantUserId) || $this->hrCanMessageRecruiter((int) $user['id'], $participantUserId, $data)) {
                return;
            }
        }

        if (in_array('job_seeker', $roles, true) && $this->candidateCanMessageHr((int) $user['id'], $participantUserId)) {
            return;
        }

        if (in_array('recruiter', $roles, true) && $this->recruiterCanMessageHr((int) $user['id'], $participantUserId, $data)) {
            return;
        }

        throw new HttpException('You are not allowed to start this conversation.', 403);
    }

    private function hrCanMessageCandidate(int $hrUserId, int $candidateUserId): bool
    {
        $profile = $this->profiles->findByUserId($candidateUserId);

        return $profile !== null && (int) ($profile['assigned_hr_officer_id'] ?? 0) === $hrUserId;
    }

    private function hrCanMessageRecruiter(int $hrUserId, int $recruiterUserId, array $data): bool
    {
        $recruiter = $this->recruiters->findByUserId($recruiterUserId);
        $job = isset($data['job_id']) ? $this->jobs->findById((int) $data['job_id']) : null;

        return $recruiter !== null
            && $job !== null
            && (int) $job['recruiter_id'] === (int) $recruiter['id']
            && (int) ($job['assigned_hr_officer_id'] ?? 0) === $hrUserId;
    }

    private function candidateCanMessageHr(int $candidateUserId, int $hrUserId): bool
    {
        $profile = $this->profiles->findByUserId($candidateUserId);

        return $profile !== null && (int) ($profile['assigned_hr_officer_id'] ?? 0) === $hrUserId;
    }

    private function recruiterCanMessageHr(int $recruiterUserId, int $hrUserId, array $data): bool
    {
        $recruiter = $this->recruiters->findByUserId($recruiterUserId);
        $job = isset($data['job_id']) ? $this->jobs->findById((int) $data['job_id']) : null;

        return $recruiter !== null
            && $job !== null
            && (int) $job['recruiter_id'] === (int) $recruiter['id']
            && (int) ($job['assigned_hr_officer_id'] ?? 0) === $hrUserId;
    }

    private function requireConversation(int $conversationId): array
    {
        $conversation = $this->conversations->findById($conversationId);

        if ($conversation === null) {
            throw new HttpException('Conversation not found.', 404);
        }

        return $conversation;
    }

    private function requireParticipant(int $conversationId, int $userId): void
    {
        if (! $this->conversations->userIsParticipant($conversationId, $userId)) {
            throw new HttpException('You are not a participant in this conversation.', 403);
        }
    }

    private function notifyParticipants(array $conversation, int $senderId, string $title, string $body, string $type): void
    {
        foreach ($this->conversations->participants((int) $conversation['id']) as $participant) {
            if ((int) $participant['id'] === $senderId) {
                continue;
            }

            $this->notifications->notify((int) $participant['id'], $title, $body, $type, [
                'conversation_id' => (int) $conversation['id'],
                'job_id' => $conversation['job_id'],
            ]);
        }
    }
}
