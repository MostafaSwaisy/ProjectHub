<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class BoardController extends Controller
{
    /**
     * Display a paginated list of boards for a project with columns and tasks.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $boards = $project->boards()
            ->with(['columns.tasks'])
            ->paginate(15);

        return response()->json(BoardResource::collection($boards));
    }

    /**
     * Store a newly created board in storage.
     *
     * @param Project $project
     * @param StoreBoardRequest $request
     * @return JsonResponse
     */
    public function store(Project $project, StoreBoardRequest $request): JsonResponse
    {
        $this->authorize('update', $project);

        $board = $project->boards()->create($request->validated());

        // Load relationships with eager loading
        $board->load(['columns.tasks']);

        return response()->json(new BoardResource($board), 201);
    }

    /**
     * Display the specified board with columns and tasks.
     *
     * @param Board $board
     * @return JsonResponse
     */
    public function show(Board $board): JsonResponse
    {
        $this->authorize('view', $board->project);

        $board->load(['columns.tasks']);

        return response()->json(new BoardResource($board));
    }

    /**
     * Update the specified board in storage.
     *
     * @param Board $board
     * @param UpdateBoardRequest $request
     * @return JsonResponse
     */
    public function update(Board $board, UpdateBoardRequest $request): JsonResponse
    {
        $this->authorize('update', $board->project);

        $board->update($request->validated());

        // Reload relationships with eager loading
        $board->load(['columns.tasks']);

        return response()->json(new BoardResource($board));
    }

    /**
     * Remove the specified board from storage.
     *
     * @param Board $board
     * @return JsonResponse
     */
    public function destroy(Board $board): JsonResponse
    {
        $this->authorize('delete', $board->project);

        $board->delete();

        return response()->json(null, 204);
    }
}
