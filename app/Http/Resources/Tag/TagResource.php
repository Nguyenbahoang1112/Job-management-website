<?php

namespace App\Http\Resources\Tag;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagResource extends JsonResource
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
            'name' => $this->name,
            'is_admin_created' => $this->is_admin_created,
            'user_id' => $this->user_id,
        ];
    }
}
