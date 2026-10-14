<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function findById(int $id): ?Conversation
    {
        return Conversation::with([
            'members.user',
            'lastMessage',
            'creator',
        ])->find($id);
    }

    public function getForUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Conversation::whereUserIsMember($userId)
            ->with([
                'members.user',
                'lastMessage.sender',
                'creator',
            ])
            ->withCount(['messages as unread_count' => function ($query) use ($userId) {
                $query->whereNull('messages.deleted_at')
                    ->where(function ($q) use ($userId) {
                        $q->whereRaw('NOT EXISTS (
                        SELECT 1 FROM conversation_members
                        WHERE conversation_members.conversation_id = messages.conversation_id
                        AND conversation_members.user_id = ?
                        AND conversation_members.last_read_message_id IS NOT NULL
                        AND messages.id <= conversation_members.last_read_message_id
                    )', [$userId]);
                    });
            }])
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findPrivateConversation(int $userId1, int $userId2): ?Conversation
    {
        return Conversation::where('type', 'private')
            ->whereHas('members', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('members', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->whereDoesntHave('members', function ($query) use ($userId1, $userId2) {
                $query->whereNotIn('user_id', [$userId1, $userId2]);
            })
            ->first();
    }

    public function create(array $data): Conversation
    {
        return DB::transaction(function () use ($data) {
            $conversation = Conversation::create([
                'type' => $data['type'],
                'name' => $data['name'] ?? null,
                'image' => $data['image'] ?? null,
                'created_by' => $data['created_by'],
            ]);

            // Add creator as member with owner role
            ConversationMember::create([
                'conversation_id' => $conversation->id,
                'user_id' => $data['created_by'],
                'role' => $conversation->isGroup() ? 'owner' : 'member',
                'joined_at' => now(),
            ]);

            // Add other members if provided
            if (! empty($data['member_ids'])) {
                $memberData = [];
                foreach ($data['member_ids'] as $memberId) {
                    if ($memberId !== $data['created_by']) {
                        $memberData[] = [
                            'conversation_id' => $conversation->id,
                            'user_id' => $memberId,
                            'role' => 'member',
                            'joined_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                if (! empty($memberData)) {
                    ConversationMember::insert($memberData);
                }
            }

            return $conversation->load(['members.user', 'creator']);
        });
    }

    public function delete(Conversation $conversation): bool
    {
        return DB::transaction(function () use ($conversation) {
            $conversation->messages()->delete();
            $conversation->members()->delete();

            return $conversation->delete();
        });
    }

    public function updateLastMessage(int $conversationId, int $messageId): void
    {
        Conversation::where('id', $conversationId)->update([
            'last_message_id' => $messageId,
            'last_message_at' => now(),
        ]);
    }

    public function getMembers(Conversation $conversation): iterable
    {
        return $conversation->members()->with('user')->get();
    }

    public function getUnreadCount(int $userId): int
    {
        return Conversation::whereUserIsMember($userId)
            ->whereHas('messages', function ($query) use ($userId) {
                $query->whereNull('messages.deleted_at')
                    ->where(function ($q) use ($userId) {
                        $q->whereRaw('NOT EXISTS (
                        SELECT 1 FROM conversation_members
                        WHERE conversation_members.conversation_id = messages.conversation_id
                        AND conversation_members.user_id = ?
                        AND conversation_members.last_read_message_id IS NOT NULL
                        AND messages.id <= conversation_members.last_read_message_id
                    )', [$userId]);
                    });
            })
            ->count();
    }
}
