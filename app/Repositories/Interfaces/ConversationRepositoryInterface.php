<?php

namespace App\Repositories\Interfaces;

use App\Models\Conversation;
use Illuminate\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation;

    public function getForUser(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function findPrivateConversation(int $userId1, int $userId2): ?Conversation;

    public function create(array $data): Conversation;

    public function delete(Conversation $conversation): bool;

    public function updateLastMessage(int $conversationId, int $messageId): void;

    public function getMembers(Conversation $conversation): iterable;

    public function getUnreadCount(int $userId): int;
}
