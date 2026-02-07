<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'task_id' => $this->task_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'body' => $this->body,
            'edited_at' => $this->edited_at?->toISOString(),
            'is_editable' => $this->isEditable(),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    /**
     * Check if the comment is still within the edit window.
     */
    private function isEditable(): bool
    {
        $currentUser = auth()->user();
        if (!$currentUser || $this->user_id !== $currentUser->id) {
            return false;
        }

        $editWindowMinutes = 15;
        $cutoffTime = $this->created_at->addMinutes($editWindowMinutes);

        return now()->lt($cutoffTime);
    }
}
