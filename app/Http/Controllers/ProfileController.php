<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Get current user's profile with preferences
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('role', 'projects', 'preferences');

        return response()->json([
            'data' => $user,
            'preferences' => $user->preferences?->pluck('value', 'key')->toArray() ?? [],
        ]);
    }

    /**
     * Update user profile (name, email, bio)
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'updated_profile'])
            ->log('Updated profile');

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png', 'max:5120'], // 5MB
        ]);

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar_url && Storage::disk('public')->exists('avatars/' . $user->avatar_url)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar_url);
        }

        // Store new avatar
        $file = $request->file('avatar');
        $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = Storage::disk('public')->putFileAs('avatars', $file, $filename);

        // Update user
        $user->update(['avatar_url' => $filename]);

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'uploaded_avatar'])
            ->log('Uploaded avatar');

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'data' => $user->fresh(),
            'avatar_url' => $user->avatar_url,
        ]);
    }

    /**
     * Delete user avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        // Delete avatar file if exists
        if ($user->avatar_url && Storage::disk('public')->exists('avatars/' . $user->avatar_url)) {
            Storage::disk('public')->delete('avatars/' . $user->avatar_url);
        }

        // Clear avatar_url from user
        $user->update(['avatar_url' => null]);

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'deleted_avatar'])
            ->log('Deleted avatar');

        return response()->json([
            'message' => 'Avatar deleted successfully.',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Your current password is required.',
            'password.required' => 'A new password is required.',
            'password.min' => 'Your password must be at least 8 characters.',
            'password.confirmed' => 'The passwords do not match.',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => ['current_password' => ['The current password is incorrect.']],
            ], 422);
        }

        // Check new password is different from old
        if (Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'The new password must be different from the current password.',
                'errors' => ['password' => ['The new password must be different from the current password.']],
            ], 422);
        }

        // Update password
        $user->update(['password' => $request->input('password')]);

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'changed_password'])
            ->log('Changed password');

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'notification_frequency' => ['nullable', 'string', 'in:realtime,daily,weekly,none'],
            'notification_email' => ['nullable', 'boolean'],
            'notification_assignments' => ['nullable', 'boolean'],
            'notification_comments' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();

        // Update or create preferences
        $preferences = [
            'notification_frequency' => $request->input('notification_frequency', 'realtime'),
            'notification_email' => $request->input('notification_email', true),
            'notification_assignments' => $request->input('notification_assignments', true),
            'notification_comments' => $request->input('notification_comments', true),
        ];

        foreach ($preferences as $key => $value) {
            $user->preferences()
                ->updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
        }

        // Log activity
        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['action' => 'updated_preferences'])
            ->log('Updated preferences');

        return response()->json([
            'message' => 'Preferences updated successfully.',
            'preferences' => $preferences,
        ]);
    }
}
