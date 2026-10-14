<?php

namespace App\Http\Resources\Chat;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender' => UserResource::make($this->whenLoaded('sender')),
            'message' => $this->message,
            'type' => $this->type,
            'is_edited' => $this->isEdited(),
            'is_deleted' => $this->isDeleted(),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'edited_at' => $this->edited_at,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
