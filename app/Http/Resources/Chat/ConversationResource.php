<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $currentUser = $request->user();
        $currentMember = $this->members->firstWhere('user_id', $currentUser?->id);

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->when($this->isGroup(), $this->name),
            'image' => $this->image_url,
            'created_by' => UserResource::make($this->whenLoaded('creator')),
            'members' => $this->whenLoaded('members', function () {
                return UserResource::collection($this->members->pluck('user'));
            }),
            'member_count' => $this->when($this->isGroup(), function () {
                return $this->members->count();
            }),
            'last_message' => MessageResource::make($this->whenLoaded('lastMessage')),
            'last_message_at' => $this->last_message_at?->diffForHumans(),
            'unread_count' => $this->unread_count ?? 0,
            'current_user_role' => $currentMember?->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
