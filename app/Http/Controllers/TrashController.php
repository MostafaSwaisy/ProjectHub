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
}
