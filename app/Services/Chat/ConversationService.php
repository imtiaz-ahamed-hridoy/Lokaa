<?php

namespace App\Services\Chat;

use App\Events\Chat\ConversationCreated;
use App\Events\Chat\MessageRead;
use App\Models\Conversation;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationService
{
    public function __construct(
        private readonly ConversationRepositoryInterface $conversationRepository,
        private readonly MessageRepositoryInterface $messageRepository,
    ) {}

    public function listConversations(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->conversationRepository->getForUser($userId, $perPage);
    }

    public function findById(int $id): ?Conversation
    {
        return $this->conversationRepository->findById($id);
    }

    public function createConversation(array $data, int $createdBy): Conversation
    {
        return DB::transaction(function () use ($data, $createdBy) {
            // For private chat, check if conversation already exists
            if ($data['type'] === 'private' && ! empty($data['member_ids'])) {
                $otherUserId = $data['member_ids'][0];
                $existing = $this->conversationRepository->findPrivateConversation($createdBy, $otherUserId);

                if ($existing) {
                    return $existing->load(['members.user', 'creator', 'lastMessage']);
                }

                $data['member_ids'] = [$otherUserId];
            }

            $conversation = $this->conversationRepository->create([
                'type' => $data['type'],
                'name' => $data['name'] ?? null,
                'image' => $data['image'] ?? null,
                'created_by' => $createdBy,
                'member_ids' => $data['member_ids'] ?? [],
            ]);

            // Broadcast conversation created event
            try {
                broadcast(new ConversationCreated($conversation));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast ConversationCreated event: '.$e->getMessage());
            }

            return $conversation;
        });
    }

    public function deleteConversation(Conversation $conversation): bool
    {
        return DB::transaction(function () use ($conversation) {
            return $this->conversationRepository->delete($conversation);
        });
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->conversationRepository->getUnreadCount($userId);
    }

    public function markAsRead(int $conversationId, int $userId, int $messageId): void
    {
        DB::transaction(function () use ($conversationId, $userId, $messageId) {
            $this->messageRepository->markAsRead($conversationId, $userId, $messageId);

            try {
                $conversation = $this->conversationRepository->findById($conversationId);
                if ($conversation) {
                    broadcast(new MessageRead($conversation, $userId, $messageId));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast MessageRead event: '.$e->getMessage());
            }
        });
    }
}
