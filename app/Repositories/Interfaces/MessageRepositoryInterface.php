<?php

namespace App\Repositories\Interfaces;

use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    public function findById(int $id): ?Message;

    public function getForConversation(int $conversationId, int $perPage = 30): LengthAwarePaginator;

    public function create(array $data): Message;

    public function update(Message $message, array $data): Message;

    public function softDelete(Message $message): bool;

    public function markAsRead(int $conversationId, int $userId, int $messageId): void;

    public function getLastMessageForConversation(int $conversationId): ?Message;
}
