<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\MoveTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskDetailResource;
use App\Models\Activity;
use App\Models\Task;
use App\Models\Column;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a paginated list of tasks with filters (column_id, assignee_id, priority, due_date).
     * Authorization is handled per-task through the Task policy.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        // Build base query
        $query = Task::with(['assignee', 'labels', 'subtasks'])
            ->withCount(['subtasks', 'labels']);

        // Apply column filter
        if ($request->has('column_id')) {
            $query->where('column_id', $request->input('column_id'));
        }

        // Apply assignee filter
        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->input('assignee_id'));
        }

        // Apply priority filter
        if ($request->has('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Apply due_date filter (tasks due on or after given date)
        if ($request->has('due_date')) {
            $query->where('due_date', '>=', $request->input('due_date'));
        }

        // Apply due_date range filter
        if ($request->has('due_date_from') && $request->has('due_date_to')) {
            $query->whereBetween('due_date', [
                $request->input('due_date_from'),
                $request->input('due_date_to'),
            ]);
        }

        // Order by position or created_at
        $tasks = $query->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created task in a column.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request)
    {
        $validated = $request->validated();
        $columnId = $validated['column_id'];

        // Get the column and authorize
        $column = Column::findOrFail($columnId);
        $this->authorize('create', $column);

        // Get the next position
        $nextPosition = $column->tasks()->max('position') ?? 0;
        $nextPosition++;

        // Create task with position
        $task = Task::create([
            ...$validated,
            'position' => $nextPosition,
        ]);

        // Log activity
        $this->logActivity($task, 'task.created', [
            'task_title' => $task->title,
        ]);

        // Load relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    /**
     * Display a single task with all relationships (subtasks, comments, labels).
     *
     * @param Task $task
     * @return TaskDetailResource
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['assignee', 'labels', 'subtasks', 'comments.user']);
        $task->loadCount(['subtasks', 'labels', 'comments']);

        return new TaskDetailResource($task);
    }

    /**
     * Update a task (title, description, assignee, priority, due_date).
     *
     * @param Task $task
     * @param UpdateTaskRequest $request
     * @return JsonResponse
     */
    public function update(Task $task, UpdateTaskRequest $request)
    {
        $this->authorize('update', $task);

        $validated = $request->validated();
        $oldValues = $task->only(array_keys($validated));

        $task->update($validated);

        // Build changes array for activity log
        $changes = [];
        foreach ($validated as $key => $newValue) {
            if (isset($oldValues[$key]) && $oldValues[$key] !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValues[$key],
                    'new' => $newValue,
                ];
            }
        }

        // Log activity if there were changes
        if (!empty($changes)) {
            $this->logActivity($task, 'task.updated', [
                'task_title' => $task->title,
                'changes' => $changes,
            ]);
        }

        // Reload relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return new TaskResource($task);
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        // Log activity before deletion
        $this->logActivity($task, 'task.deleted', [
            'task_title' => $task->title,
            'task_id' => $task->id,
        ]);

        // Delete related data (subtasks, comments, labels pivot)
        $task->subtasks()->delete();
        $task->comments()->delete();
        $task->labels()->detach();

        $task->delete();

        return response()->noContent();
    }

    /**
     * Handle drag-and-drop: update column_id and position, check WIP limit.
     *
     * @param Task $task
     * @param MoveTaskRequest $request
     * @return JsonResponse
     */
    public function move(Task $task, MoveTaskRequest $request)
    {
        $this->authorize('update', $task);

        $validated = $request->validated();
        $newColumnId = $validated['column_id'];
        $newPosition = $validated['position'];

        // Store original column for activity logging
        $oldColumnId = $task->column_id;
        $oldColumn = $task->column;

        // Get the new column
        $newColumn = Column::findOrFail($newColumnId);

        // Check if the task is moving to a different column
        if ($task->column_id !== $newColumnId) {
            // Check WIP limit on target column (0 means unlimited)
            $currentTaskCount = $newColumn->tasks()->count();

            if ($newColumn->wip_limit > 0 && $currentTaskCount >= $newColumn->wip_limit) {
                return response()->json([
                    'message' => 'Column WIP limit exceeded',
                    'wip_limit' => $newColumn->wip_limit,
                    'current_count' => $currentTaskCount,
                ], 422);
            }

            // Update column_id
            $task->column_id = $newColumnId;
        }

        // Update position
        $task->position = $newPosition;
        $task->save();

        // Log activity if column changed
        if ($oldColumnId !== $newColumnId) {
            $this->logActivity($task, 'task.moved', [
                'task_title' => $task->title,
                'from_column' => $oldColumn?->title,
                'to_column' => $newColumn->title,
            ]);
        }

        // Reorder tasks in target column based on positions
        $this->reorderTasksInColumn($newColumn);

        // Load relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return new TaskResource($task);
    }

    /**
     * Reorder all tasks in a column to have sequential positions.
     *
     * @param Column $column
     * @return void
     */
    private function reorderTasksInColumn(Column $column): void
    {
        $tasks = $column->tasks()->orderBy('position')->get();

        foreach ($tasks as $index => $task) {
            $task->update(['position' => $index]);
        }
    }

    /**
     * Sync labels for a task.
     *
     * @param Task $task
     * @param Request $request
     * @return TaskResource
     */
    public function syncLabels(Task $task, Request $request)
    {
        $this->authorize('update', $task);

        $request->validate([
            'label_ids' => 'required|array',
            'label_ids.*' => 'integer|exists:labels,id',
        ]);

        $labelIds = $request->input('label_ids');

        // Get current labels for activity logging
        $currentLabelIds = $task->labels()->pluck('labels.id')->toArray();

        // Sync labels
        $task->labels()->sync($labelIds);

        // Log activity for new labels
        $addedLabels = array_diff($labelIds, $currentLabelIds);
        $removedLabels = array_diff($currentLabelIds, $labelIds);

        foreach ($addedLabels as $labelId) {
            $label = $task->labels()->find($labelId);
            if ($label) {
                $this->logActivity($task, 'label.assigned', [
                    'label_id' => $labelId,
                    'label_name' => $label->name,
                ]);
            }
        }

        foreach ($removedLabels as $labelId) {
            $this->logActivity($task, 'label.removed', [
                'label_id' => $labelId,
            ]);
        }

        // Reload relationships
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return new TaskResource($task);
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
