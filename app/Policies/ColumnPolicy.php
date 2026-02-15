<?php

namespace App\Policies;

use App\Models\Column;
use App\Models\User;

class ColumnPolicy
{
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
}
