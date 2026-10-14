<?php

namespace App\Http\Resources\Marketplace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarketplaceDashboardRecentMessagesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currentUser = $request->user();
        $conversation = $this->resource;

        // Determine the display name and avatar based on conversation type
        $displayName = null;
        $displayAvatar = null;

        if ($conversation->isPrivate()) {
            // For private chats, get the other participant's info
            $otherMember = $conversation->members->firstWhere('user_id', '!=', $currentUser?->id);
            if ($otherMember && $otherMember->user) {
                $displayName = $otherMember->user->name;
                $displayAvatar = $otherMember->user->avatar_url;
            } else {
                $displayName = 'Unknown User';
                $displayAvatar = asset('images/default-avatar.png');
            }
        } else {
            // For groups, use the group name and image
            $displayName = $conversation->name ?: 'Unnamed Group';
            $displayAvatar = $conversation->image_url;
        }

        return [
            'id' => $conversation->id,
            'name' => $displayName,
            'avatar' => $displayAvatar,
            'last_message' => $conversation->lastMessage?->message,
            'last_message_at' => $conversation->last_message_at?->diffForHumans(),
            'is_online' => false, // Online status is handled via Reverb presence channels
        ];
    }
}
