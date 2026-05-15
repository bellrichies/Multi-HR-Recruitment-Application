<?php

declare(strict_types=1);

namespace App\Modules\Messaging\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Modules\Messaging\Requests\CreateConversationRequest;
use App\Modules\Messaging\Requests\SendMessageRequest;
use App\Modules\Messaging\Resources\ConversationResource;
use App\Modules\Messaging\Resources\MessageResource;
use App\Modules\Messaging\Services\MessagingService;

class ConversationController extends Controller
{
    public function __construct(
        private readonly MessagingService $messaging,
        private readonly CreateConversationRequest $conversationRequest,
        private readonly SendMessageRequest $messageRequest
    ) {
    }

    public function index(Request $request): void
    {
        $result = $this->messaging->list($request->user(), (int) $request->query('page', 1), (int) $request->query('per_page', 20));

        $this->success(ConversationResource::collection($result['data']), 'Conversations retrieved successfully.', $result['meta']);
    }

    public function store(Request $request): void
    {
        $payload = $this->messaging->create($this->conversationRequest->validate($request), $request->user(), $this->context($request));

        $this->success(
            ConversationResource::make($payload['conversation'], $payload['participants']),
            'Conversation created successfully.',
            [],
            201
        );
    }

    public function messages(Request $request, string $id): void
    {
        $result = $this->messaging->messages((int) $id, $request->user(), (int) $request->query('page', 1), (int) $request->query('per_page', 50));

        $this->success(MessageResource::collection($result['data']), 'Messages retrieved successfully.', $result['meta']);
    }

    public function send(Request $request, string $id): void
    {
        $message = $this->messaging->send((int) $id, $this->messageRequest->validate($request), $request->user(), $this->context($request));

        $this->success(MessageResource::make($message), 'Message sent successfully.', [], 201);
    }

    public function unreadCount(Request $request): void
    {
        $this->success(['unread_count' => $this->messaging->unreadCount($request->user())], 'Unread message count retrieved successfully.');
    }

    private function context(Request $request): array
    {
        return ['actor_id' => $request->user()['id'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()];
    }
}
