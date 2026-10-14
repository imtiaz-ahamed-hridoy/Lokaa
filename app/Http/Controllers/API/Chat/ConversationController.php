<?php

namespace App\Http\Controllers\API\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Chat\CreateConversationRequest;
use App\Http\Resources\Chat\ConversationResource;
use App\Models\Conversation;
use App\Services\Chat\ConversationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ConversationService $conversationService,
    ) {}

    /**
     * Get all conversations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $conversations = $this->conversationService->listConversations(
                $request->user()->id,
                min($perPage, 50)
            );

            return $this->pagination(
                'Conversations retrieved successfully.',
                ConversationResource::collection($conversations->items()),
                $conversations
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Create a new conversation (private or group).
     */
    public function store(CreateConversationRequest $request): JsonResponse
    {
        try {
            $conversation = $this->conversationService->createConversation(
                $request->validated(),
                $request->user()->id
            );

            return $this->success(
                $conversation->isPrivate() ? 'Conversation created successfully.' : 'Group created successfully.',
                ConversationResource::make($conversation),
                201
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Get a single conversation details.
     */
    public function show(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('view', $conversation);

            $conversation->load(['members.user', 'creator', 'lastMessage.sender']);

            return $this->success(
                'Conversation retrieved successfully.',
                ConversationResource::make($conversation)
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('delete', $conversation);

            $this->conversationService->deleteConversation($conversation);

            return $this->success('Conversation deleted successfully.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Get unread conversations count.
     */
    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $count = $this->conversationService->getUnreadCount($request->user()->id);

            return $this->success('Unread count retrieved.', [
                'unread_count' => $count,
            ]);
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }
}
