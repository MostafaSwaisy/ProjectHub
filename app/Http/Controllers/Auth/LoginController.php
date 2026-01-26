<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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
        // Attempt to authenticate user
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        try {
            $user = Auth::user();

            // Generate Sanctum token
            $token = $user->createToken('API Token');

            return response()->json([
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                ],
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
