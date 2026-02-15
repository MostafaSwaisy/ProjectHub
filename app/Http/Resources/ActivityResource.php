<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'type' => $this->type,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'data' => $this->data,
            'message' => $this->formatMessage(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }

    /**
     * Format a human-readable message for the activity.
     */
    private function formatMessage(): string
    {
        $userName = $this->user?->name ?? 'Someone';
        $data = $this->data ?? [];

        return match ($this->type) {
            'task.created' => "{$userName} created task \"{$data['title'] ?? 'Untitled'}\"",
            'task.updated' => "{$userName} updated task",
            'task.moved' => "{$userName} moved task from {$data['from_column'] ?? 'unknown'} to {$data['to_column'] ?? 'unknown'}",
            'task.assigned' => "{$userName} assigned task to {$data['assignee_name'] ?? 'someone'}",
            'task.due_date_changed' => "{$userName} changed due date",
            'task.deleted' => "{$userName} deleted task \"{$data['title'] ?? 'Untitled'}\"",
            'subtask.created' => "{$userName} added subtask \"{$data['title'] ?? 'Untitled'}\"",
            'subtask.completed' => "{$userName} completed subtask \"{$data['title'] ?? 'Untitled'}\"",
            'subtask.uncompleted' => "{$userName} uncompleted subtask \"{$data['title'] ?? 'Untitled'}\"",
            'comment.created' => "{$userName} commented: \"{$data['excerpt'] ?? '...'}\"",
            'label.assigned' => "{$userName} added label \"{$data['label_name'] ?? 'unknown'}\"",
            'label.removed' => "{$userName} removed label \"{$data['label_name'] ?? 'unknown'}\"",
            default => "{$userName} performed an action",
        };
    }
}
