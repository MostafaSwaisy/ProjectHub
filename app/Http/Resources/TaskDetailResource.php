<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * This resource includes full details including comments for task detail views.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'column_id' => $this->column_id,
            'title' => $this->title,
            'description' => $this->description,
            'assignee_id' => $this->assignee_id,
            'assignee' => new UserResource($this->whenLoaded('assignee')),
            'priority' => $this->priority,
            'due_date' => $this->due_date?->toDateString(),
            'position' => $this->position,
            'subtask_count' => $this->subtasks_count ?? $this->subtasks()->count(),
            'completed_subtask_count' => $this->completed_subtask_count,
            'comment_count' => $this->comments_count ?? $this->comments()->count(),
            'label_count' => $this->labels_count ?? $this->labels()->count(),
            'labels' => LabelResource::collection($this->whenLoaded('labels')),
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'progress' => $this->progress,
            'is_overdue' => $this->isOverdue(),
            'can_update' => $user ? $user->can('update', $this->resource) : false,
            'can_delete' => $user ? $user->can('delete', $this->resource) : false,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
