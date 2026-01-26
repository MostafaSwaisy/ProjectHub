<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\MoveTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\Column;
use Illuminate\Http\JsonResponse;
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
    public function index(Request $request): JsonResponse
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

        return response()->json(TaskResource::collection($tasks));
    }

    /**
     * Store a newly created task in a column.
     *
     * @param StoreTaskRequest $request
     * @return JsonResponse
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $columnId = $validated['column_id'];

        // Get the column and authorize
        $column = Column::findOrFail($columnId);
        $this->authorize('create', [$column]);

        // Get the next position
        $nextPosition = $column->tasks()->max('position') ?? 0;
        $nextPosition++;

        // Create task with position
        $task = Task::create([
            ...$validated,
            'position' => $nextPosition,
        ]);

        // Load relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return response()->json(new TaskResource($task), 201);
    }

    /**
     * Display a single task with all relationships (subtasks, comments, labels).
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return response()->json(new TaskResource($task));
    }

    /**
     * Update a task (title, description, assignee, priority, due_date).
     *
     * @param Task $task
     * @param UpdateTaskRequest $request
     * @return JsonResponse
     */
    public function update(Task $task, UpdateTaskRequest $request): JsonResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        // Reload relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return response()->json(new TaskResource($task));
    }

    /**
     * Delete a task.
     *
     * @param Task $task
     * @return JsonResponse
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }

    /**
     * Handle drag-and-drop: update column_id and position, check WIP limit.
     *
     * @param Task $task
     * @param MoveTaskRequest $request
     * @return JsonResponse
     */
    public function move(Task $task, MoveTaskRequest $request): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validated();
        $newColumnId = $validated['column_id'];
        $newPosition = $validated['position'];

        // Get the new column
        $newColumn = Column::findOrFail($newColumnId);

        // Check if the task is moving to a different column
        if ($task->column_id !== $newColumnId) {
            // Check WIP limit on target column
            $currentTaskCount = $newColumn->tasks()->count();

            if ($newColumn->wip_limit !== null && $currentTaskCount >= $newColumn->wip_limit) {
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

        // Reorder tasks in target column based on positions
        $this->reorderTasksInColumn($newColumn);

        // Load relationships with eager loading
        $task->load(['assignee', 'labels', 'subtasks']);
        $task->loadCount(['subtasks', 'labels']);

        return response()->json(new TaskResource($task));
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
}
