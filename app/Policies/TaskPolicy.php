<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Models\Column;

class TaskPolicy
{
    /**
     * Determine if the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the task.
     * - User must be a member of the project that contains the task
     * - Admins can view all tasks
     */
    public function view(User $user, Task $task): bool
    {
        // Get the project through the task's column and board
        $project = $task->column->board->project;

        // Admin can view all tasks
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Instructor who owns the project can view all tasks
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // User must be a member of the project
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can create a task in the column.
     * - User must be a member of the project
     */
    public function create(User $user, Column $column): bool
    {
        // Get the project through the column's board
        $project = $column->board->project;

        // Admin can create tasks in any project
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Instructor who owns the project can create tasks
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // Other users must be a member of the project
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can update the task.
     * - Any project member can update the task (including drag-and-drop moves)
     * - Project admin (instructor or admin) can update any task
     */
    public function update(User $user, Task $task): bool
    {
        // Get the project through the task's column and board
        $project = $task->column->board->project;

        // Admin can update all tasks
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Project owner (instructor) can update tasks
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // Any project member can update tasks
        return $project->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine if the user can delete the task.
     * - Task assignee can delete the task
     * - Project admin (instructor or admin) can delete the task
     */
    public function delete(User $user, Task $task): bool
    {
        // Get the project through the task's column and board
        $project = $task->column->board->project;

        // Admin can delete all tasks
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Project owner (instructor) can delete tasks
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // Assignee can delete the task
        if ($task->assignee_id && $task->assignee_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    /**
     * Determine if the user can permanently delete the task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }
}
