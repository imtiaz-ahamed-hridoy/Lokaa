<?php

namespace App\Repositories;

use App\Models\ConversationMember;
use App\Models\Message;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MessageRepository implements MessageRepositoryInterface
{
    public function findById(int $id): ?Message
    {
        return Message::with([
            'sender',
            'attachments',
            'conversation.members.user',
        ])->withTrashed()->find($id);
    }

    public function getForConversation(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::where('conversation_id', $conversationId)
            ->with([
                'sender',
                'attachments',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Message
    {
        return DB::transaction(function () use ($data) {
            $message = Message::create([
                'conversation_id' => $data['conversation_id'],
                'sender_id' => $data['sender_id'],
                'message' => $data['message'],
                'type' => $data['type'] ?? 'text',
            ]);

            if (! empty($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $message->attachments()->create([
                        'file' => $attachment['file'],
                        'mime_type' => $attachment['mime_type'],
                        'size' => $attachment['size'],
                    ]);
                }
            }

            return $message->load(['sender', 'attachments']);
        });
    }

    public function update(Message $message, array $data): Message
    {
        $message->update([
            'message' => $data['message'],
            'edited_at' => now(),
        ]);

        return $message->fresh(['sender', 'attachments']);
    }

    public function softDelete(Message $message): bool
    {
        return $message->delete();
    }

    public function markAsRead(int $conversationId, int $userId, int $messageId): void
    {
        ConversationMember::where('conversation_id', $conversationId)
            ->where('user_id', $userId)
            ->where(function ($query) use ($messageId) {
                $query->whereNull('last_read_message_id')
                    ->orWhere('last_read_message_id', '<', $messageId);
            })
            ->update([
                'last_read_message_id' => $messageId,
            ]);
    }

    public function getLastMessageForConversation(int $conversationId): ?Message
    {
        return Message::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
