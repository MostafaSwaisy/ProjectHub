<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLabelRequest;
use App\Http\Requests\UpdateLabelRequest;
use App\Http\Resources\LabelResource;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    /**
     * Display a listing of labels for a project.
     */
    public function index(Project $project): AnonymousResourceCollection
    {
        $labels = $project->labels()->orderBy('name')->get();

        return LabelResource::collection($labels);
    }

    /**
     * Store a newly created label.
     */
    public function store(StoreLabelRequest $request, Project $project): JsonResponse
    {
        // Check policy - only project owner/instructor can create labels
        if (Gate::denies('create', [Label::class, $project])) {
            return response()->json([
                'message' => 'Only project owners can create labels.',
            ], 403);
        }

        $validated = $request->validated();
        $validated['project_id'] = $project->id;

        $label = Label::create($validated);

        return (new LabelResource($label))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified label.
     */
    public function update(UpdateLabelRequest $request, Project $project, Label $label): LabelResource|JsonResponse
    {
        // Check policy
        if (Gate::denies('update', $label)) {
            return response()->json([
                'message' => 'Only project owners can update labels.',
            ], 403);
        }

        $validated = $request->validated();
        $label->update($validated);

        return new LabelResource($label);
    }

    /**
     * Remove the specified label.
     * This also removes the label from all tasks.
     */
    public function destroy(Project $project, Label $label): JsonResponse
    {
        // Check policy
        if (Gate::denies('delete', $label)) {
            return response()->json([
                'message' => 'Only project owners can delete labels.',
            ], 403);
        }

        // Detach from all tasks first (handled by cascade, but being explicit)
        $label->tasks()->detach();
        $label->delete();

        return response()->json(null, 204);
    }
}
