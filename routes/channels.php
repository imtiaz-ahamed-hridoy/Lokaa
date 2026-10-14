<?php

use App\Models\ConversationMember;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Private channel for specific conversations
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    return ConversationMember::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
}, ['guards' => ['web', 'sanctum']]);

// Private channel for user-specific notifications
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
}, ['guards' => ['web', 'sanctum']]);

// Presence channel for online users
Broadcast::channel('online-users', function ($user) {
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->avatar_url,
        ];
    }

    return false;
}, ['guards' => ['web', 'sanctum']]);
