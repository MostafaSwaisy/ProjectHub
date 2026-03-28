<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * List all invitations for a project.
     */
    public function index(Project $project): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        $invitations = $project->invitations()
            ->with('inviter:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $invitations]);
    }

    /**
     * Send a new invitation to join the project.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => 'required|in:lead,member,viewer',
        ]);

        // Check if already a member
        $alreadyMember = $project->members()->where('email', $validated['email'])->exists()
            || $project->instructor->email === $validated['email'];
        if ($alreadyMember) {
            return response()->json(['message' => 'User is already a project member.'], 422);
        }

        // Check for existing pending invitation
        $existing = $project->invitations()
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->whereDate('expires_at', '>=', now())
            ->first();

        if ($existing) {
            return response()->json(['message' => 'A pending invitation already exists for this email.'], 422);
        }

        $invitation = $project->invitations()->create([
            'invited_by' => Auth::id(),
            'email'      => $validated['email'],
            'role'       => $validated['role'],
            'token'      => Invitation::generateToken(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['data' => $invitation->load('inviter:id,name,email')], 201);
    }

    /**
     * Resend an invitation (regenerate token and reset expiry).
     */
    public function resend(Project $project, Invitation $invitation): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        if ($invitation->project_id !== $project->id) {
            return response()->json(['message' => 'Invitation not found in this project.'], 404);
        }

        $invitation->update([
            'token'      => Invitation::generateToken(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['data' => $invitation->fresh()]);
    }

    /**
     * Cancel / delete an invitation.
     */
    public function destroy(Project $project, Invitation $invitation): JsonResponse
    {
        $this->authorize('manageMembers', $project);

        if ($invitation->project_id !== $project->id) {
            return response()->json(['message' => 'Invitation not found in this project.'], 404);
        }

        $invitation->delete();

        return response()->json(null, 204);
    }

    /**
     * Accept an invitation by token (public — no project param needed).
     */
    public function accept(string $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending' || $invitation->isExpired()) {
            return response()->json(['message' => 'This invitation is no longer valid.'], 422);
        }

        $user = Auth::user();

        if ($user->email !== $invitation->email) {
            return response()->json(['message' => 'This invitation was sent to a different email address.'], 403);
        }

        // Add to project
        ProjectMember::firstOrCreate(
            ['project_id' => $invitation->project_id, 'user_id' => $user->id],
            ['role' => $invitation->role]
        );

        $invitation->accept();

        return response()->json(['message' => 'Invitation accepted.', 'project_id' => $invitation->project_id]);
    }

    /**
     * Decline an invitation by token.
     */
    public function decline(string $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if ($invitation->status !== 'pending') {
            return response()->json(['message' => 'This invitation is no longer valid.'], 422);
        }

        $user = Auth::user();

        if ($user->email !== $invitation->email) {
            return response()->json(['message' => 'This invitation was sent to a different email address.'], 403);
        }

        $invitation->decline();

        return response()->json(['message' => 'Invitation declined.']);
    }

    /**
     * List pending invitations sent to the current user's email.
     */
    public function pending(): JsonResponse
    {
        $user = Auth::user();

        $invitations = Invitation::with(['project:id,title', 'inviter:id,name,email'])
            ->where('email', $user->email)
            ->where('status', 'pending')
            ->whereDate('expires_at', '>=', now())
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $invitations]);
    }
}
