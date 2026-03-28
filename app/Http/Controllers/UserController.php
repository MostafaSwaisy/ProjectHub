<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Get paginated list of users with search, filter, and sort
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by system role
        if ($request->has('role_id') && $request->input('role_id') !== null) {
            $query->where('role_id', $request->input('role_id'));
        }

        // Filter by status (active/deleted)
        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'deleted') {
                $query->onlyTrashed();
            } elseif ($status === 'active') {
                $query->whereNull('deleted_at');
            }
        }

        // Sort
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $query->orderBy($sort, $direction);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $users = $query->paginate($perPage);

        // Add related data
        $users->load('role', 'projects');

        // Add computed fields
        return response()->json($users);
    }

    /**
     * Get single user with related data
     */
    public function show(User $user): JsonResponse
    {
        $user->load('role', 'projects', 'projectMembers');

        return response()->json([
            'data' => $user,
            'projects_count' => $user->projects->count(),
            'is_admin' => $user->role?->name === 'admin',
            'is_instructor' => $user->role?->name === 'instructor',
            'is_student' => $user->role?->name === 'student',
        ]);
    }

    /**
     * Update user (admin only)
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['action' => 'updated_user'])
            ->log('Updated user: ' . $user->name);

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => $user->fresh()->load('role', 'projects'),
        ]);
    }

    /**
     * Soft-delete user (admin only, cannot delete self)
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Prevent deleting self
        if ($request->user()->id === $user->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        // Soft-delete the user
        $user->delete();

        // Log activity
        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties(['action' => 'deleted_user'])
            ->log('Deleted user: ' . $user->name);

        // Unassign all tasks from this user
        $user->assignedTasks()->update(['assignee_id' => null]);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
