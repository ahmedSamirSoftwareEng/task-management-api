<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'assigned_to' => $this->assigned_to,
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return new UserResource($this->assignedUser);
            }),
            'due_date' => $this->due_date,
            'dependencies' => TaskResource::collection($this->whenLoaded('dependencies')),
            'dependents' => TaskResource::collection($this->whenLoaded('dependents')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
