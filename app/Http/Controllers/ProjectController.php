<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Board;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreProjectRequest;
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
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Will be implemented in T040
    }

    /**
     * Archive a project
     */
    public function archive(Project $project)
    {
        // Will be implemented in T048
    }

    /**
     * Unarchive a project
     */
    public function unarchive(Project $project)
    {
        // Will be implemented in T049
    }

    /**
     * Duplicate a project
     */
    public function duplicate(Request $request, Project $project)
    {
        // Will be implemented in T084
    }

    /**
     * List project members
     */
    public function members(Project $project)
    {
        // Will be implemented in T072
    }

    /**
     * Add a member to the project
     */
    public function addMember(Request $request, Project $project)
    {
        // Will be implemented in T073
    }

    /**
     * Update a member's role
     */
    public function updateMember(Request $request, Project $project, $userId)
    {
        // Will be implemented in T074
    }

    /**
     * Remove a member from the project
     */
    public function removeMember(Project $project, $userId)
    {
        // Will be implemented in T075
    }
}
