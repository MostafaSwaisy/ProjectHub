<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for authenticated user
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        $userId = $user->id;

        // Cache results for 5 minutes per user (300 seconds)
        $stats = Cache::remember("dashboard.stats.{$userId}", 300, function () use ($userId) {
            return [
                'total_projects' => $this->getTotalProjects($userId),
                'active_tasks' => $this->getActiveTasks($userId),
                'team_members' => $this->getTeamMembers($userId),
                'overdue_tasks' => $this->getOverdueTasks($userId),
            ];
        });

        return response()->json([
            'data' => [
                'stats' => $stats
            ]
        ], 200);
    }

    /**
     * Count projects where user is instructor OR member
     *
     * @param int $userId
     * @return int
     */
    private function getTotalProjects(int $userId): int
    {
        return DB::table('projects')
            ->leftJoin('project_members', 'projects.id', '=', 'project_members.project_id')
            ->where(function ($query) use ($userId) {
                $query->where('projects.instructor_id', $userId)
                    ->orWhere('project_members.user_id', $userId);
            })
            ->distinct()
            ->count('projects.id');
    }

    /**
     * Count tasks NOT in "Done" or "Archived" columns across user's projects
     *
     * @param int $userId
     * @return int
     */
    private function getActiveTasks(int $userId): int
    {
        return DB::table('tasks')
            ->join('columns', 'tasks.column_id', '=', 'columns.id')
            ->join('boards', 'columns.board_id', '=', 'boards.id')
            ->join('projects', 'boards.project_id', '=', 'projects.id')
            ->leftJoin('project_members', 'projects.id', '=', 'project_members.project_id')
            ->where(function ($query) use ($userId) {
                $query->where('projects.instructor_id', $userId)
                    ->orWhere('project_members.user_id', $userId);
            })
            ->whereNotIn('columns.title', ['Done', 'Archived'])
            ->count('tasks.id');
    }

    /**
     * Count distinct OTHER users who are members of user's projects (excludes self)
     *
     * @param int $userId
     * @return int
     */
    private function getTeamMembers(int $userId): int
    {
        // Get all project IDs where user is instructor or member
        $projectIds = DB::table('projects')
            ->select('projects.id')
            ->leftJoin('project_members as pm', 'projects.id', '=', 'pm.project_id')
            ->where(function ($query) use ($userId) {
                $query->where('projects.instructor_id', $userId)
                    ->orWhere('pm.user_id', $userId);
            })
            ->pluck('id');

        // Count distinct members in those projects (excluding self)
        return DB::table('project_members')
            ->whereIn('project_id', $projectIds)
            ->where('user_id', '!=', $userId)
            ->distinct()
            ->count('user_id');
    }

    /**
     * Count tasks with due_date < today AND NOT in "Done"/"Archived" columns
     *
     * @param int $userId
     * @return int
     */
    private function getOverdueTasks(int $userId): int
    {
        return DB::table('tasks')
            ->join('columns', 'tasks.column_id', '=', 'columns.id')
            ->join('boards', 'columns.board_id', '=', 'boards.id')
            ->join('projects', 'boards.project_id', '=', 'projects.id')
            ->leftJoin('project_members', 'projects.id', '=', 'project_members.project_id')
            ->where(function ($query) use ($userId) {
                $query->where('projects.instructor_id', $userId)
                    ->orWhere('project_members.user_id', $userId);
            })
            ->where('tasks.due_date', '<', now()->toDateString())
            ->whereNotIn('columns.title', ['Done', 'Archived'])
            ->count('tasks.id');
    }
}
