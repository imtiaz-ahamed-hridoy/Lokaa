<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file' => $this->file_url,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'created_at' => $this->created_at,
        ];
    }
}
