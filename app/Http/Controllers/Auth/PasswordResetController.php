<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send password reset email.
     *
     * @param PasswordResetRequest $request
     * @return JsonResponse
     */
    public function sendReset(PasswordResetRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->validated('email'))->first();

            if (!$user) {
                return response()->json([
                    'message' => 'If an account with that email exists, a password reset link has been sent.',
                ], 200);
            }

            // Generate a unique reset token
            $resetToken = Str::random(64);

            // Store the reset token in the user model (you would need to add a column for this)
            // For now, we'll use Laravel's Password broker
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link has been sent to your email address.',
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to send password reset link.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing your request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify and reset password with token.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            // Validate the reset token and update password
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Password has been reset successfully.',
                ], 200);
            }

            // Token expired or invalid
            if ($status === Password::INVALID_TOKEN) {
                return response()->json([
                    'message' => 'The password reset token is invalid.',
                ], 422);
            }

            if ($status === Password::INVALID_USER) {
                return response()->json([
                    'message' => 'We could not find a user with that email address.',
                ], 422);
            }

            return response()->json([
                'message' => 'Password reset failed.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while resetting your password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
