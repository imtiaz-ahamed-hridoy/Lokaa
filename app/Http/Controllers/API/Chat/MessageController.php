<?php

namespace App\Http\Controllers\API\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Chat\SendMessageRequest;
use App\Http\Requests\API\Chat\UpdateMessageRequest;
use App\Http\Resources\Chat\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Chat\ConversationService;
use App\Services\Chat\MessageService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly MessageService $messageService,
    ) {}

    /**
     * Get messages for a conversation.
     */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('viewMessages', $conversation);

            $perPage = (int) $request->input('per_page', 30);
            $messages = $this->messageService->getMessages(
                $conversation->id,
                min($perPage, 100)
            );

            return $this->pagination(
                'Messages retrieved successfully.',
                MessageResource::collection($messages->items()),
                $messages
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Send a new message.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        try {
            $this->authorize('sendMessage', [Conversation::findOrFail($request->conversation_id)]);

            $message = $this->messageService->sendMessage(
                $request->validated(),
                $request->user()->id
            );

            return $this->success(
                'Message sent successfully.',
                MessageResource::make($message),
                201
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Update a message.
     */
    public function update(UpdateMessageRequest $request, Message $message): JsonResponse
    {
        try {
            $this->authorize('update', $message);

            $updated = $this->messageService->updateMessage(
                $message,
                $request->input('message')
            );

            return $this->success(
                'Message updated successfully.',
                MessageResource::make($updated)
            );
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Soft delete a message.
     */
    public function destroy(Request $request, Message $message): JsonResponse
    {
        try {
            $this->authorize('delete', $message);

            $this->messageService->deleteMessage($message);

            return $this->success('Message deleted successfully.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Broadcast typing indicator.
     */
    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('view', $conversation);

            $this->messageService->broadcastTyping(
                $conversation->id,
                $request->user()->id,
                $request->user()->name
            );

            return $this->success('Typing indicator sent.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Broadcast stopped typing indicator.
     */
    public function stoppedTyping(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('view', $conversation);

            $this->messageService->broadcastStoppedTyping(
                $conversation->id,
                $request->user()->id
            );

            return $this->success('Stopped typing indicator sent.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }

    /**
     * Mark messages as read in a conversation.
     */
    public function markAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        try {
            $this->authorize('view', $conversation);

            $request->validate([
                'message_id' => 'required|integer|exists:messages,id',
            ]);

            // Use the conversation service for marking as read
            $conversationService = app(ConversationService::class);
            $conversationService->markAsRead(
                $conversation->id,
                $request->user()->id,
                $request->input('message_id')
            );

            return $this->success('Messages marked as read.');
        } catch (\Throwable $e) {
            return $this->safeError($e);
        }
    }
}
