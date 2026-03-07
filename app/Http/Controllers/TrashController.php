<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Board;
use App\Models\Column;
use App\Models\Subtask;
use App\Models\Comment;
use App\Http\Resources\TrashItemResource;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class TrashController extends Controller
{
    /**
     * Constructor to set up authorization middleware
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of soft-deleted items in the project trash.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Project $project)
    {
        // Authorize the action - user must be project member
        $this->authorize('view', $project);

        // Get query parameters
        $type = $request->input('type'); // Optional: filter by type (tasks|boards|columns|subtasks|comments)
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        $perPage = min($perPage, 100); // Max 100 items per page

        // Collect all soft-deleted items from different entity types
        $deletedItems = collect();

        // Fetch deleted tasks for this project
        if (!$type || $type === 'tasks') {
            $deletedTasks = Task::onlyTrashed()
                ->whereHas('column.board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->with(['column.board', 'deletedBy'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'task',
                        'title' => $item->title,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deleted_by ?? null,
                        'model_instance' => $item,
                    ];
                });
            $deletedItems = $deletedItems->merge($deletedTasks);
        }

        // Fetch deleted boards for this project
        if (!$type || $type === 'boards') {
            $deletedBoards = Board::onlyTrashed()
                ->where('project_id', $project->id)
                ->with('deletedBy')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'board',
                        'title' => $item->title,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deleted_by ?? null,
                        'model_instance' => $item,
                    ];
                });
            $deletedItems = $deletedItems->merge($deletedBoards);
        }

        // Fetch deleted columns for this project
        if (!$type || $type === 'columns') {
            $deletedColumns = Column::onlyTrashed()
                ->whereHas('board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->with(['board', 'deletedBy'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'column',
                        'title' => $item->title,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deleted_by ?? null,
                        'model_instance' => $item,
                    ];
                });
            $deletedItems = $deletedItems->merge($deletedColumns);
        }

        // Fetch deleted subtasks for this project
        if (!$type || $type === 'subtasks') {
            $deletedSubtasks = Subtask::onlyTrashed()
                ->whereHas('task.column.board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->with(['task', 'deletedBy'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'subtask',
                        'title' => $item->title,
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deleted_by ?? null,
                        'model_instance' => $item,
                    ];
                });
            $deletedItems = $deletedItems->merge($deletedSubtasks);
        }

        // Fetch deleted comments for this project
        if (!$type || $type === 'comments') {
            $deletedComments = Comment::onlyTrashed()
                ->whereHas('task.column.board', function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                })
                ->with(['task', 'user', 'deletedBy'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'type' => 'comment',
                        'title' => substr($item->body, 0, 100), // First 100 chars of comment
                        'deleted_at' => $item->deleted_at,
                        'deleted_by' => $item->deleted_by ?? null,
                        'model_instance' => $item,
                    ];
                });
            $deletedItems = $deletedItems->merge($deletedComments);
        }

        // Sort by deleted_at descending (newest first)
        $deletedItems = $deletedItems->sortByDesc('deleted_at');

        // Manual pagination since we're working with collections
        $total = $deletedItems->count();
        $items = $deletedItems
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        // Transform items using TrashItemResource
        $transformedItems = TrashItemResource::collection($items);

        // Return paginated response
        return response()->json([
            'data' => $transformedItems,
            'meta' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    /**
     * Restore a soft-deleted item from the trash.
     *
     * Handles restore for all 5 entity types: tasks, subtasks, comments, boards, columns.
     * For tasks with orphaned column parents, requires column_id in request body.
     * Cascades restore to children via HasCascadeSoftDeletes trait.
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(Request $request, Project $project)
    {
        // Authorize the action
        $this->authorize('update', $project);

        // Validate request
        $validated = $request->validate([
            'type' => 'required|in:task,subtask,comment,board,column',
            'id' => 'required|integer',
            'column_id' => 'nullable|integer', // For orphaned tasks only
        ]);

        $type = $validated['type'];
        $id = $validated['id'];
        $columnId = $validated['column_id'] ?? null;

        try {
            $model = null;

            // Fetch the trashed model
            switch ($type) {
                case 'task':
                    $model = Task::withTrashed()->findOrFail($id);

                    // Check if the column (parent) still exists
                    if ($model->column && !$model->column->trashed()) {
                        // Column exists and is not trashed, proceed with restore
                        $model->restore();
                    } elseif ($columnId) {
                        // Column is orphaned or deleted, but user provided a new column_id
                        // Update the column_id before restoring
                        $newColumn = Column::withTrashed()->findOrFail($columnId);

                        // Check if new column is in the same board
                        if ($newColumn->board_id !== $model->column->board_id) {
                            return response()->json([
                                'message' => 'The selected column is not in the same board.',
                            ], 422);
                        }

                        $model->column_id = $columnId;
                        $model->save();
                        $model->restore();
                    } else {
                        // Column is orphaned and no columnId provided, return 409
                        $board = $model->column->board;
                        if ($board->trashed()) {
                            return response()->json([
                                'message' => 'Cannot restore task: parent column\'s board has been permanently deleted.',
                                'type' => 'board_deleted',
                            ], 409);
                        }

                        // Get available columns in the board
                        $availableColumns = Column::where('board_id', $board->id)
                            ->whereNull('deleted_at')
                            ->orderBy('position')
                            ->get(['id', 'title', 'position'])
                            ->toArray();

                        return response()->json([
                            'message' => 'Task parent column has been deleted. Please select a new parent column.',
                            'type' => 'orphaned_task',
                            'available_columns' => $availableColumns,
                        ], 409);
                    }
                    break;

                case 'subtask':
                    $model = Subtask::withTrashed()->findOrFail($id);

                    // Check if parent task still exists and is not trashed
                    if ($model->task && !$model->task->trashed()) {
                        $model->restore();
                    } else {
                        return response()->json([
                            'message' => 'Cannot restore subtask: parent task has been deleted.',
                            'type' => 'orphaned_subtask',
                        ], 409);
                    }
                    break;

                case 'comment':
                    $model = Comment::withTrashed()->findOrFail($id);

                    // Check if parent task still exists and is not trashed
                    if ($model->task && !$model->task->trashed()) {
                        $model->restore();
                    } else {
                        return response()->json([
                            'message' => 'Cannot restore comment: parent task has been deleted.',
                            'type' => 'orphaned_comment',
                        ], 409);
                    }
                    break;

                case 'board':
                    $model = Board::withTrashed()->findOrFail($id);

                    // Verify board belongs to this project
                    if ($model->project_id !== $project->id) {
                        return response()->json([
                            'message' => 'Board not found in this project.',
                        ], 404);
                    }

                    $model->restore();
                    break;

                case 'column':
                    $model = Column::withTrashed()->findOrFail($id);

                    // Check if parent board still exists and is not trashed
                    if ($model->board && !$model->board->trashed()) {
                        $model->restore();
                    } else {
                        return response()->json([
                            'message' => 'Cannot restore column: parent board has been deleted.',
                            'type' => 'orphaned_column',
                        ], 409);
                    }
                    break;
            }

            // Ensure the model exists
            if (!$model) {
                return response()->json([
                    'message' => 'Item not found in trash.',
                ], 404);
            }

            // Create activity log
            Activity::create([
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'type' => 'restored',
                'subject_type' => get_class($model),
                'subject_id' => $model->id,
                'data' => [
                    'item_type' => $type,
                    'item_title' => $model->title ?? substr($model->body ?? 'N/A', 0, 50),
                ],
            ]);

            // Return restored item
            return response()->json([
                'data' => new TrashItemResource([
                    'id' => $model->id,
                    'type' => $type,
                    'title' => $model->title ?? substr($model->body ?? 'N/A', 0, 50),
                    'deleted_at' => $model->deleted_at,
                    'deleted_by' => $model->deletedBy,
                    'model_instance' => $model,
                ]),
                'message' => 'Item restored successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Item not found.',
            ], 404);
        }
    }
}
