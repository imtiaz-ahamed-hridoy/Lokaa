<?php

namespace App\Policies;

use App\Models\ConversationMember;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    public function update(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id && ! $message->isDeleted();
    }

    public function delete(User $user, Message $message): bool
    {
        if ($message->sender_id === $user->id) {
            return true;
        }

        // Check if user is admin/owner of the group
        if ($message->conversation->isGroup()) {
            return ConversationMember::where('conversation_id', $message->conversation_id)
                ->where('user_id', $user->id)
                ->whereIn('role', ['owner', 'admin'])
                ->exists();
        }

        return false;
    }

    public function view(User $user, Message $message): bool
    {
        return ConversationMember::where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->exists();
    }
}
