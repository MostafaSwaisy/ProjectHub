<?php

namespace App\Policies;

use App\Models\Label;
use App\Models\Project;
use App\Models\User;

class LabelPolicy
{
    /**
     * Determine if the user can create labels in the project.
     * Only project owner (instructor role) can create labels.
     */
    public function create(User $user, Project $project): bool
    {
        return $this->isProjectOwner($user, $project);
    }

    /**
     * Determine if the user can update the label.
     * Only project owner can update labels.
     */
    public function update(User $user, Label $label): bool
    {
        $project = $label->project;
        return $project && $this->isProjectOwner($user, $project);
    }

    /**
     * Determine if the user can delete the label.
     * Only project owner can delete labels.
     */
    public function delete(User $user, Label $label): bool
    {
        $project = $label->project;
        return $project && $this->isProjectOwner($user, $project);
    }

    /**
     * Check if user is the project owner (instructor).
     */
    private function isProjectOwner(User $user, Project $project): bool
    {
        // Check if user is the project instructor
        if ($project->instructor_id === $user->id) {
            return true;
        }

        // Check if user has instructor role in project members
        $member = $project->members()
            ->where('user_id', $user->id)
            ->first();

        return $member && $member->pivot->role === 'instructor';
    }
}
