<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        // Calculate task completion statistics
        // BUG-013: Use preloaded counts from withCount() when available to avoid N+1
        $totalTasks = $this->tasks_count ?? $this->tasks()->count();
        // BUG-011: Match common done-column names instead of hardcoded 'Done'
        $completedTasks = $this->completed_tasks_count ?? $this->tasks()
            ->whereHas('column', function ($query) {
                $query->whereIn('title', ['Done', 'Completed', 'Complete', 'Finished', 'Closed']);
            })
            ->count();
        $activeTasks = $totalTasks - $completedTasks;

        $taskCompletion = [
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'active' => $activeTasks,
            'percentage' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
        ];

        // Get members (limited to 5 for list view performance, all for show view)
        $members = $this->whenLoaded('members', function () {
            return UserResource::collection($this->members->take(5));
        });

        // Calculate permissions for current user
        $isOwner = $user && $user->id === $this->instructor_id;
        // BUG-012: Avoid extra query if members relation is already loaded
        $membership = $user
            ? ($this->relationLoaded('members')
                ? $this->members->firstWhere('user_id', $user->id)
                : $this->members()->where('user_id', $user->id)->first())
            : null;
        $userRole = $membership ? $membership->pivot->role : null;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'timeline_status' => $this->timeline_status ?? 'On Track',
            'budget_status' => $this->budget_status ?? 'Within Budget',
            'is_archived' => (bool) $this->is_archived,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'instructor_id' => $this->instructor_id,
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'members' => $members,
            'total_members' => 1 + ($this->members ? $this->members->count() : 0),
            'task_completion' => $taskCompletion,
            // Permissions — roles are now: owner, lead, member, viewer (editor was renamed to member)
            'permissions' => [
                'can_edit' => $isOwner || in_array($userRole, ['lead', 'member']),
                'can_delete' => $isOwner,
                'can_archive' => $isOwner,
                'can_manage_members' => $isOwner || $userRole === 'lead',
            ],
        ];
    }
}
