<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image_url,
            'type' => $this->type,
            'created_by' => UserResource::make($this->whenLoaded('creator')),
            'members' => $this->whenLoaded('members', function () {
                return $this->members->map(function ($member) {
                    return [
                        'user' => UserResource::make($member->user),
                        'role' => $member->role,
                        'joined_at' => $member->joined_at,
                    ];
                });
            }),
            'member_count' => $this->members_count ?? $this->when($this->relationLoaded('members'), $this->members->count()),
            'last_message_at' => $this->last_message_at?->diffForHumans(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
