<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'column_id' => $this->column_id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee_id' => $this->assignee_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'position' => $this->position,
            'subtask_count' => $this->subtasks_count ?? $this->subtasks()->count(),
            'label_count' => $this->labels_count ?? $this->labels()->count(),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
