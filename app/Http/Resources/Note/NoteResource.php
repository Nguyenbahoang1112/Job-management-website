<?php

namespace App\Http\Resources\Note;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->timezone('Asia/Ho_Chi_Minh')->toDateTimeString(),
            'updated_at' => $this->updated_at->timezone('Asia/Ho_Chi_Minh')->toDateTimeString(),
        ];
    }
}
