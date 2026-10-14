<?php

namespace App\Services\Chat;

use App\Events\Chat\MessageDeleted;
use App\Events\Chat\MessageSent;
use App\Events\Chat\MessageUpdated;
use App\Events\Chat\UserStoppedTyping;
use App\Events\Chat\UserTyping;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageService
{
    public function __construct(
        private readonly MessageRepositoryInterface $messageRepository,
        private readonly ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function getMessages(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return $this->messageRepository->getForConversation($conversationId, $perPage);
    }

    public function sendMessage(array $data, int $senderId): Message
    {
        return DB::transaction(function () use ($data, $senderId) {
            $message = $this->messageRepository->create([
                'conversation_id' => $data['conversation_id'],
                'sender_id' => $senderId,
                'message' => $data['message'] ?? '',
                'type' => $data['type'] ?? 'text',
                'attachments' => $data['attachments'] ?? [],
            ]);

            // Update conversation last message
            $this->conversationRepository->updateLastMessage(
                $data['conversation_id'],
                $message->id
            );

            // Broadcast message sent event
            try {
                broadcast(new MessageSent($message));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast MessageSent event: '.$e->getMessage());
            }

            return $message;
        });
    }

    public function updateMessage(Message $message, string $newMessage): Message
    {
        return DB::transaction(function () use ($message, $newMessage) {
            $updated = $this->messageRepository->update($message, ['message' => $newMessage]);

            try {
                broadcast(new MessageUpdated($updated));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast MessageUpdated event: '.$e->getMessage());
            }

            return $updated;
        });
    }

    public function deleteMessage(Message $message): bool
    {
        return DB::transaction(function () use ($message) {
            $deleted = $this->messageRepository->softDelete($message);

            if ($deleted) {
                try {
                    broadcast(new MessageDeleted($message));
                } catch (\Throwable $e) {
                    Log::error('Failed to broadcast MessageDeleted event: '.$e->getMessage());
                }
            }

            return $deleted;
        });
    }

    public function broadcastTyping(int $conversationId, int $userId, string $userName): void
    {
        try {
            broadcast(new UserTyping($conversationId, $userId, $userName));
        } catch (\Throwable $e) {
            Log::error('Failed to broadcast UserTyping event: '.$e->getMessage());
        }
    }

    public function broadcastStoppedTyping(int $conversationId, int $userId): void
    {
        try {
            broadcast(new UserStoppedTyping($conversationId, $userId));
        } catch (\Throwable $e) {
            Log::error('Failed to broadcast UserStoppedTyping event: '.$e->getMessage());
        }
    }
}
