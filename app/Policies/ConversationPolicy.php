<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\ConversationMember;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Conversation $conversation): bool
    {
        if ($conversation->isPrivate()) {
            return $conversation->created_by === $user->id;
        }

        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->where('role', 'owner')
            ->exists();
    }

    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function viewMessages(User $user, Conversation $conversation): bool
    {
        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function addMember(User $user, Conversation $conversation): bool
    {
        if (! $conversation->isGroup()) {
            return false;
        }

        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function removeMember(User $user, Conversation $conversation): bool
    {
        if (! $conversation->isGroup()) {
            return false;
        }

        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function updateGroup(User $user, Conversation $conversation): bool
    {
        if (! $conversation->isGroup()) {
            return false;
        }

        return ConversationMember::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
}
