<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle user logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            // Delete the current access token (Sanctum)
            $request->user()->currentAccessToken()->delete();

            // Return success response
            return response()->json([
                'message' => 'Logout successful.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
