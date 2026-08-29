<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more roles.
 * Usage in routes: ->middleware('role:admin')
 *
 * This is the enforcement point for RBAC described in the spec:
 * Authentication -> Authorization -> Role -> Allowed Data -> AI -> Response
 * It runs BEFORE any controller or AI logic executes.
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! in_array($user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden. You do not have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}
