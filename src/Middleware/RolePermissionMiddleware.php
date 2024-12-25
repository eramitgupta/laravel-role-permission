<?php

namespace EragPermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, $role = null, $permission = null): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized action.');
        }

        if (! $request->user()->hasRole($role)) {
            abort(403, 'You do not have the required role.');
        }
        if ($permission !== null && ! $request->user()->hasPermissions($permission)) {
            abort(403, 'You do not have the required permission.');
        }

        return $next($request);
    }
}
