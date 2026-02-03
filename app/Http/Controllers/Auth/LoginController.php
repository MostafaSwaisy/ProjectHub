<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Handle user login.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        // Get remember me preference
        $remember = $request->input('remember', false);

        // Attempt to authenticate user with remember me option
        if (!Auth::attempt($request->only('email', 'password'), $remember)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        try {
            $user = Auth::user();

            // Generate Sanctum token
            $token = $user->createToken('auth_token');

            return response()->json([
                'message' => 'Login successful.',
                'user' => new UserResource($user),
                'token' => $token->plainTextToken,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
