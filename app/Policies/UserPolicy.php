<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the target user.
     * - Admins can view all users
     * - Instructors can view their assigned students
     * - Students can view only themselves
     */
    public function view(User $user, User $target): bool
    {
        // Admin can view all users
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Students can only view themselves
        if ($user->role && $user->role->name === 'student') {
            return $user->id === $target->id;
        }

        // Instructors can view their assigned students and themselves
        if ($user->role && $user->role->name === 'instructor') {
            // Can view self
            if ($user->id === $target->id) {
                return true;
            }

            // Can view students in their projects
            $hasStudentInProject = $user->ownedProjects()
                ->whereHas('members', function ($query) use ($target) {
                    $query->where('user_id', $target->id);
                })
                ->exists();

            return $hasStudentInProject;
        }

        return false;
    }

    /**
     * Determine if the user can view the profile of the target user.
     * Same rules as view()
     */
    public function viewProfile(User $user, User $target): bool
    {
        return $this->view($user, $target);
    }

    /**
     * Determine if the user can update the target user.
     * - Admins can update all users
     * - Users can update their own profile
     */
    public function update(User $user, User $target): bool
    {
        // Admin can update all users
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // User can update their own profile
        return $user->id === $target->id;
    }

    /**
     * Determine if the user can create a user.
     * - Only admins can create users
     */
    public function create(User $user): bool
    {
        return $user->role && $user->role->name === 'admin';
    }

    /**
     * Determine if the user can delete the target user.
     * - Only admins can delete users
     */
    public function delete(User $user, User $target): bool
    {
        return $user->role && $user->role->name === 'admin';
    }

    /**
     * Determine if the user can restore the target user.
     */
    public function restore(User $user, User $target): bool
    {
        return $this->delete($user, $target);
    }

    /**
     * Determine if the user can permanently delete the target user.
     */
    public function forceDelete(User $user, User $target): bool
    {
        return $this->delete($user, $target);
    }
}
