<?php

namespace App\Repositories;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Support\Facades\DB;

class GroupRepository implements GroupRepositoryInterface
{
    public function createGroup(array $data, int $createdBy, array $memberIds = []): Conversation
    {
        return DB::transaction(function () use ($data, $createdBy, $memberIds) {
            $conversation = Conversation::create([
                'type' => 'group',
                'name' => $data['name'],
                'image' => $data['image'] ?? null,
                'created_by' => $createdBy,
            ]);

            // Add creator as owner
            ConversationMember::create([
                'conversation_id' => $conversation->id,
                'user_id' => $createdBy,
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            // Add other members
            $memberData = [];
            foreach ($memberIds as $memberId) {
                if ($memberId !== $createdBy) {
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

            return $conversation->load(['members.user', 'creator']);
        });
    }

    public function updateGroup(Conversation $conversation, array $data): Conversation
    {
        $conversation->update([
            'name' => $data['name'] ?? $conversation->name,
            'image' => $data['image'] ?? $conversation->image,
        ]);

        return $conversation->fresh(['members.user', 'creator']);
    }

    public function addMember(Conversation $conversation, int $userId, string $role = 'member'): ConversationMember
    {
        return ConversationMember::create([
            'conversation_id' => $conversation->id,
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => now(),
        ]);
    }

    public function removeMember(Conversation $conversation, int $userId): bool
    {
        return (bool) ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function updateMemberRole(ConversationMember $member, string $role): ConversationMember
    {
        $member->update(['role' => $role]);

        return $member->fresh();
    }

    public function getMember(Conversation $conversation, int $userId): ?ConversationMember
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->first();
    }

    public function isMember(Conversation $conversation, int $userId): bool
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->exists();
    }

    public function isOwnerOrAdmin(Conversation $conversation, int $userId): bool
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $userId)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function leaveGroup(Conversation $conversation, int $userId): bool
    {
        return DB::transaction(function () use ($conversation, $userId) {
            $deleted = $this->removeMember($conversation, $userId);

            if ($deleted && ConversationMember::where('conversation_id', $conversation->id)->count() === 0) {
                $conversation->delete();
            }

            return $deleted;
        });
    }

    public function countMembers(Conversation $conversation): int
    {
        return ConversationMember::where('conversation_id', $conversation->id)->count();
    }

    public function getMemberIds(Conversation $conversation): array
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->pluck('user_id')
            ->toArray();
    }
}
