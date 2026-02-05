<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Board;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Constructor to set up authorization middleware
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Build query
        $query = Project::forUser($user->id)
            ->with(['instructor', 'members' => function ($query) {
                $query->take(5);
            }]);

        // Filter by archived status
        $archived = $request->boolean('archived', false);
        if ($archived) {
            $query->archived();
        } else {
            $query->active();
        }

        // Filter by timeline status
        if ($request->filled('status')) {
            $query->where('timeline_status', $request->status);
        }

        // Filter by role
        if ($request->filled('role')) {
            if ($request->role === 'owner') {
                $query->where('instructor_id', $user->id);
            } elseif ($request->role === 'member') {
                $query->where('instructor_id', '!=', $user->id)
                    ->whereHas('members', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'updated_at');
        $sortOrder = $request->input('order', 'desc');

        // Map frontend sort fields to database columns
        $sortMap = [
            'updated_at' => 'updated_at',
            'created_at' => 'created_at',
            'title' => 'title',
        ];

        $sortColumn = $sortMap[$sortField] ?? 'updated_at';
        $query->orderBy($sortColumn, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 20);
        $perPage = min($perPage, 100); // Max 100 items per page

        $projects = $query->paginate($perPage);

        return response()->json([
            'data' => ProjectResource::collection($projects),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProjectRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProjectRequest $request)
    {
        // Authorize the action
        $this->authorize('create', Project::class);

        // Use database transaction to ensure project and board are created together
        $project = DB::transaction(function () use ($request) {
            // Create the project
            $project = Project::create([
                'title' => $request->title,
                'description' => $request->description,
                'timeline_status' => $request->timeline_status ?? 'On Track',
                'budget_status' => $request->budget_status ?? 'Within Budget',
                'instructor_id' => $request->user()->id,
            ]);

            // Create default board (columns will be auto-created via Board boot method)
            Board::create([
                'project_id' => $project->id,
                'title' => 'Main Board',
            ]);

            return $project;
        });

        // Load relationships for the resource
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Project created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        // Authorize the action
        $this->authorize('view', $project);

        // Load relationships
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProjectRequest $request
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // Authorize the action
        $this->authorize('update', $project);

        // T035: Optimistic locking - check if project was modified since client loaded it
        if ($request->has('updated_at')) {
            $clientUpdatedAt = $request->updated_at;
            $serverUpdatedAt = $project->updated_at->toISOString();

            if ($clientUpdatedAt !== $serverUpdatedAt) {
                return response()->json([
                    'error' => 'conflict',
                    'message' => 'This project was modified by another user. Please refresh and try again.',
                    'current_data' => new ProjectResource($project->load(['instructor', 'members'])),
                ], 409); // 409 Conflict
            }
        }

        // Update the project
        $project->update($request->only([
            'title',
            'description',
            'timeline_status',
            'budget_status',
        ]));

        // Reload relationships
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Project updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Project $project)
    {
        // Authorize the action
        $this->authorize('delete', $project);

        // T041: Get task count for confirmation requirement
        $taskCount = $project->tasks()->count();

        // Delete the project (cascade will handle related records via DB foreign keys)
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
            'task_count' => $taskCount,
        ]);
    }

    /**
     * Archive a project
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function archive(Project $project)
    {
        // Authorize the action
        $this->authorize('archive', $project);

        // T048: Archive the project
        $project->update(['is_archived' => true]);

        // Reload relationships
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Project archived successfully',
        ]);
    }

    /**
     * Unarchive a project
     *
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function unarchive(Project $project)
    {
        // Authorize the action
        $this->authorize('archive', $project);

        // T049: Unarchive the project
        $project->update(['is_archived' => false]);

        // Reload relationships
        $project->load(['instructor', 'members']);

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Project unarchived successfully',
        ]);
    }

    /**
     * Duplicate a project
     * T084-T090: POST /api/projects/{project}/duplicate
     */
    public function duplicate(Request $request, Project $project)
    {
        // Authorize - user must be able to view the project to duplicate it
        $this->authorize('view', $project);

        // Validate request
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'include_tasks' => ['boolean'],
        ]);

        try {
            // Use transaction to ensure atomicity
            $duplicatedProject = DB::transaction(function () use ($project, $validated, $request) {
                // T084, T090: Create new project with current user as instructor
                $newProject = Project::create([
                    'title' => $validated['title'],
                    'description' => $project->description,
                    'timeline_status' => $project->timeline_status,
                    'budget_status' => $project->budget_status,
                    'instructor_id' => $request->user()->id, // Current user becomes owner
                    'is_archived' => false, // New project is always active
                ]);

                // T085: Duplicate boards and columns
                foreach ($project->boards as $board) {
                    $newBoard = $newProject->boards()->create([
                        'title' => $board->title,
                    ]);

                    // Duplicate columns
                    foreach ($board->columns as $column) {
                        $newColumn = $newBoard->columns()->create([
                            'title' => $column->title,
                            'position' => $column->position,
                        ]);

                        // T085: Optionally duplicate tasks
                        if ($validated['include_tasks'] ?? false) {
                            foreach ($column->tasks as $task) {
                                $newColumn->tasks()->create([
                                    'title' => $task->title,
                                    'description' => $task->description,
                                    'priority' => $task->priority,
                                    'due_date' => $task->due_date,
                                    'position' => $task->position,
                                    // Note: assignee_id is intentionally NOT copied
                                    // Tasks in duplicated project have no assignees
                                ]);
                            }
                        }
                    }
                }

                return $newProject;
            });

            // Load relationships for response
            $duplicatedProject->load(['instructor', 'members', 'boards']);

            return response()->json([
                'data' => new ProjectResource($duplicatedProject),
                'message' => 'Project duplicated successfully',
            ], 201);
        } catch (\Exception $e) {
            // T089: Handle duplication failure - transaction automatically rolled back
            return response()->json([
                'message' => 'Failed to duplicate project. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List project members
     * T072: GET /api/projects/{project}/members
     */
    public function members(Request $request, Project $project)
    {
        // Authorize the action
        $this->authorize('view', $project);

        // Get pagination params
        $perPage = $request->input('per_page', 20);
        $perPage = min($perPage, 100); // Max 100 members per page

        // Get members with pagination
        $members = $project->members()
            ->withPivot('role', 'created_at')
            ->orderBy('project_members.created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $members->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->created_at,
                ];
            }),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * Add a member to the project
     * T073: POST /api/projects/{project}/members
     */
    public function addMember(Request $request, Project $project)
    {
        // Authorize the action
        $this->authorize('manageMembers', $project);

        // Validate request
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'in:editor,viewer'],
        ]);

        // Check if user is already a member
        if ($project->members()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'message' => 'User is already a member of this project',
            ], 422);
        }

        // Check if trying to add the project owner
        if ($project->instructor_id == $validated['user_id']) {
            return response()->json([
                'message' => 'The project owner is already part of the project',
            ], 422);
        }

        // Add member with default Viewer role
        $project->members()->attach($validated['user_id'], [
            'role' => $validated['role'] ?? 'viewer',
        ]);

        // Reload members
        $project->load('members');

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Member added successfully',
        ], 201);
    }

    /**
     * Update a member's role
     * T074: PUT /api/projects/{project}/members/{userId}
     */
    public function updateMember(Request $request, Project $project, $userId)
    {
        // Authorize the action
        $this->authorize('manageMembers', $project);

        // Validate request
        $validated = $request->validate([
            'role' => ['required', 'in:editor,viewer'],
        ]);

        // Check if user is a member
        if (!$project->members()->where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'User is not a member of this project',
            ], 404);
        }

        // Update member role
        $project->members()->updateExistingPivot($userId, [
            'role' => $validated['role'],
        ]);

        // Reload members
        $project->load('members');

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Member role updated successfully',
        ]);
    }

    /**
     * Remove a member from the project
     * T075: DELETE /api/projects/{project}/members/{userId}
     */
    public function removeMember(Project $project, $userId)
    {
        // Authorize the action
        $this->authorize('manageMembers', $project);

        // Check if user is a member
        if (!$project->members()->where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => 'User is not a member of this project',
            ], 404);
        }

        // Prevent removing the project owner (though they shouldn't be in members table)
        if ($project->instructor_id == $userId) {
            return response()->json([
                'message' => 'Cannot remove the project owner',
            ], 422);
        }

        // Remove member
        $project->members()->detach($userId);

        // Reload members
        $project->load('members');

        return response()->json([
            'data' => new ProjectResource($project),
            'message' => 'Member removed successfully',
        ]);
    }
}
