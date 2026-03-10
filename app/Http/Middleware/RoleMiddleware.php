<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check system-level roles first
        $userRole = $request->user()->role()->first();

        // If checking for system-level roles (admin, instructor, student)
        if (!empty($roles)) {
            $systemRoles = ['admin', 'instructor', 'student'];
            $requestedSystemRoles = array_intersect($roles, $systemRoles);

            if (!empty($requestedSystemRoles)) {
                if (!$userRole || !in_array($userRole->name, $requestedSystemRoles)) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
            }

            // If checking for project-level roles (owner, lead, member, viewer)
            // These should be checked in the controller using policies
            // This middleware supports both but leaves project-level to policies
        }

        return $next($request);
    }
}
