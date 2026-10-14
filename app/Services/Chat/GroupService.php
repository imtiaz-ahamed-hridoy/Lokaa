<?php

namespace App\Services\Chat;

use App\Events\Chat\ConversationCreated;
use App\Events\Chat\GroupUpdated;
use App\Events\Chat\MemberAdded;
use App\Events\Chat\MemberRemoved;
use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupService
{
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
    ) {}

    public function createGroup(array $data, int $createdBy): Conversation
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $conversation = $this->groupRepository->createGroup(
                $data,
                $createdBy,
                $data['member_ids'] ?? []
            );

            try {
                broadcast(new ConversationCreated($conversation));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast ConversationCreated event: '.$e->getMessage());
            }

            return $conversation;
        });
    }

    public function updateGroup(Conversation $conversation, array $data): Conversation
    {
        return DB::transaction(function () use ($conversation, $data) {
            $updated = $this->groupRepository->updateGroup($conversation, $data);

            try {
                broadcast(new GroupUpdated($updated));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast GroupUpdated event: '.$e->getMessage());
            }

            return $updated;
        });
    }

    public function deleteGroup(Conversation $conversation): bool
    {
        $deleted = $conversation->delete();

        return $deleted;
    }

    public function addMember(Conversation $conversation, int $userId): ConversationMember
    {
        return DB::transaction(function () use ($conversation, $userId) {
            $member = $this->groupRepository->addMember($conversation, $userId);

            try {
                broadcast(new MemberAdded($conversation, $userId));
            } catch (\Throwable $e) {
                Log::error('Failed to broadcast MemberAdded event: '.$e->getMessage());
            }

            return $member;
        });
    }

    public function removeMember(Conversation $conversation, int $userId): bool
    {
        return DB::transaction(function () use ($conversation, $userId) {
            $removed = $this->groupRepository->removeMember($conversation, $userId);

            if ($removed) {
                try {
                    broadcast(new MemberRemoved($conversation, $userId));
                } catch (\Throwable $e) {
                    Log::error('Failed to broadcast MemberRemoved event: '.$e->getMessage());
                }
            }

            return $removed;
        });
    }

    public function leaveGroup(Conversation $conversation, int $userId): bool
    {
        return DB::transaction(function () use ($conversation, $userId) {
            $left = $this->groupRepository->leaveGroup($conversation, $userId);

            if ($left) {
                try {
                    broadcast(new MemberRemoved($conversation, $userId));
                } catch (\Throwable $e) {
                    Log::error('Failed to broadcast MemberRemoved event: '.$e->getMessage());
                }
            }

            return $left;
        });
    }
}
