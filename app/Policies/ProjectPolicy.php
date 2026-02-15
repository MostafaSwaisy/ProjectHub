<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the project.
     * - Admins can view all projects
     * - Instructors can view their own projects
     * - Students can view projects they are members of
     */
    public function view(User $user, Project $project): bool
    {
        // Admin can view all projects
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Instructor can view their own projects
        if ($user->role && $user->role->name === 'instructor' && $user->id === $project->instructor_id) {
            return true;
        }

        // Student can view projects they are members of
        if ($user->role && $user->role->name === 'student') {
            return $project->members()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Determine if the user can create projects.
     * - Only Instructors and Admins can create projects
     */
    public function create(User $user): bool
    {
        if (!$user->role) {
            return false;
        }

        return in_array($user->role->name, ['instructor', 'admin']);
    }

    /**
     * Determine if the user can update the project.
     * - Admins can update all projects
     * - Instructors can update their own projects
     * - Editors (members with editor role) can update project details
     */
    public function update(User $user, Project $project): bool
    {
        // Admin can update all projects
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Instructor can update their own projects
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // Check if user is a member with 'editor' role
        $membership = $project->members()->where('user_id', $user->id)->first();
        if ($membership && $membership->pivot->role === 'editor') {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the project.
     * - Admins can delete all projects
     * - Instructors can delete their own projects
     */
    public function delete(User $user, Project $project): bool
    {
        // Admin can delete all projects
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Instructor can delete their own projects
        if ($user->role && $user->role->name === 'instructor' && $user->id === $project->instructor_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine if the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine if the user can archive/unarchive the project.
     * - Only the project instructor (owner) can archive
     */
    public function archive(User $user, Project $project): bool
    {
        return $user->id === $project->instructor_id;
    }

    /**
     * Determine if the user can manage team members.
     * - Only the project instructor (owner) can manage members
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $user->id === $project->instructor_id;
    }
}
