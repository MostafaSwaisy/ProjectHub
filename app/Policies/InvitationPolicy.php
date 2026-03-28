<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    /**
     * Determine if the user can send (create) an invitation to the project
     * - Only project owner or lead can send invitations
     */
    public function send(User $user, Invitation $invitation): bool
    {
        $project = $invitation->project;

        // Admin can send invitations to any project
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Project owner can send invitations
        if ($user->id === $project->instructor_id) {
            return true;
        }

        // Check if user is a member with owner or lead role
        $membership = $project->members()->where('user_id', $user->id)->first();
        if ($membership) {
            $role = $membership->pivot->role;
            return in_array($role, ['owner', 'lead']);
        }

        return false;
    }

    /**
     * Determine if the user can view invitation details
     * - Only project owner or lead can view invitations
     */
    public function view(User $user, Invitation $invitation): bool
    {
        return $this->send($user, $invitation);
    }

    /**
     * Determine if the user can cancel an invitation
     * - Only the user who sent it, project owner, or admins can cancel
     */
    public function cancel(User $user, Invitation $invitation): bool
    {
        // Admin can cancel any invitation
        if ($user->role && $user->role->name === 'admin') {
            return true;
        }

        // Only the person who invited can cancel
        if ($user->id === $invitation->invited_by) {
            return true;
        }

        // Project owner can cancel any invitation
        if ($user->id === $invitation->project->instructor_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can accept an invitation
     * - Only the invited user (matching email) can accept
     */
    public function accept(User $user, Invitation $invitation): bool
    {
        // User's email must match the invitation email
        return $user->email === $invitation->email;
    }

    /**
     * Determine if the user can decline an invitation
     * - Only the invited user (matching email) can decline
     */
    public function decline(User $user, Invitation $invitation): bool
    {
        return $this->accept($user, $invitation);
    }
}
