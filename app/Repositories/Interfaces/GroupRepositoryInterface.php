<?php

namespace App\Repositories\Interfaces;

use App\Models\Conversation;
use App\Models\ConversationMember;

interface GroupRepositoryInterface
{
    public function createGroup(array $data, int $createdBy, array $memberIds = []): Conversation;

    public function updateGroup(Conversation $conversation, array $data): Conversation;

    public function addMember(Conversation $conversation, int $userId, string $role = 'member'): ConversationMember;

    public function removeMember(Conversation $conversation, int $userId): bool;

    public function updateMemberRole(ConversationMember $member, string $role): ConversationMember;

    public function getMember(Conversation $conversation, int $userId): ?ConversationMember;

    public function isMember(Conversation $conversation, int $userId): bool;

    public function isOwnerOrAdmin(Conversation $conversation, int $userId): bool;

    public function leaveGroup(Conversation $conversation, int $userId): bool;

    public function countMembers(Conversation $conversation): int;

    public function getMemberIds(Conversation $conversation): array;
}
