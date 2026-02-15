<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubtaskRequest;
use App\Http\Requests\UpdateSubtaskRequest;
use App\Http\Resources\SubtaskResource;
use App\Models\Activity;
use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubtaskController extends Controller
{
    /**
     * Display a listing of subtasks for a task.
     */
    public function index(Task $task): AnonymousResourceCollection
    {
        $subtasks = $task->subtasks()->orderBy('position')->get();

        return SubtaskResource::collection($subtasks);
    }

    /**
     * Store a newly created subtask.
     */
    public function store(StoreSubtaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        // Set position to end of list
        $maxPosition = $task->subtasks()->max('position') ?? -1;
        $validated['position'] = $maxPosition + 1;
        $validated['task_id'] = $task->id;

        $subtask = Subtask::create($validated);

        // Log activity
        $this->logActivity($task, 'subtask.created', [
            'subtask_id' => $subtask->id,
            'title' => $subtask->title,
        ]);

        return (new SubtaskResource($subtask))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified subtask.
     */
    public function update(UpdateSubtaskRequest $request, Task $task, Subtask $subtask): SubtaskResource
    {
        $validated = $request->validated();
        $wasCompleted = $subtask->is_completed;

        $subtask->update($validated);

        // Log completion activity if toggled
        if (isset($validated['is_completed']) && $validated['is_completed'] !== $wasCompleted) {
            $this->logActivity($task, $validated['is_completed'] ? 'subtask.completed' : 'subtask.uncompleted', [
                'subtask_id' => $subtask->id,
                'title' => $subtask->title,
            ]);
        }

        return new SubtaskResource($subtask);
    }

    /**
     * Remove the specified subtask.
     */
    public function destroy(Task $task, Subtask $subtask): JsonResponse
    {
        $subtask->delete();

        return response()->json(null, 204);
    }

    /**
     * Reorder subtasks within a task.
     */
    public function reorder(Request $request, Task $task): AnonymousResourceCollection
    {
        $request->validate([
            'subtask_ids' => 'required|array',
            'subtask_ids.*' => 'integer|exists:subtasks,id',
        ]);

        $subtaskIds = $request->input('subtask_ids');

        DB::transaction(function () use ($subtaskIds, $task) {
            foreach ($subtaskIds as $position => $subtaskId) {
                Subtask::where('id', $subtaskId)
                    ->where('task_id', $task->id)
                    ->update(['position' => $position]);
            }
        });

        $subtasks = $task->subtasks()->orderBy('position')->get();

        return SubtaskResource::collection($subtasks);
    }

    /**
     * Log an activity for the task.
     */
    private function logActivity(Task $task, string $type, array $data = []): void
    {
        $column = $task->column;
        $board = $column?->board;
        $projectId = $board?->project_id;

        if ($projectId) {
            Activity::create([
                'user_id' => auth()->id(),
                'project_id' => $projectId,
                'type' => $type,
                'subject_type' => Task::class,
                'subject_id' => $task->id,
                'data' => $data,
            ]);
        }
    }
}
