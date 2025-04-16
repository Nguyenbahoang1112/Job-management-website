<?php
namespace App\Http\Resources\User\TaskGroup;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskGroupResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_admin_created' => (bool) $this->is_admin_created,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}