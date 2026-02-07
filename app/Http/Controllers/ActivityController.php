<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    /**
     * Display activities for a specific task.
     */
    public function index(Request $request, Task $task): AnonymousResourceCollection
    {
        $limit = $request->input('limit', 20);

        $activities = Activity::where('subject_type', Task::class)
            ->where('subject_id', $task->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return ActivityResource::collection($activities);
    }

    /**
     * Display all activities for a project.
     */
    public function projectActivities(Request $request, Project $project): AnonymousResourceCollection
    {
        $limit = $request->input('limit', 50);
        $type = $request->input('type');

        $query = Activity::where('project_id', $project->id)
            ->with('user')
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $activities = $query->limit($limit)->get();

        return ActivityResource::collection($activities);
    }
}
