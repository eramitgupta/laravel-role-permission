<?php

namespace EragPermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $role = null, $permission = null): Response
    {
        if (! $request->user()) {
            abort(403, 'Unauthorized action.');
        }

        if (! $request->user()->hasRole($role)) {
            abort(404, 'Unauthorized action.');
        }
        if ($permission !== null && ! $request->user()->hasPermissions($permission)) {
            abort(404, 'Unauthorized action.');
        }

        return $next($request);
    }
}
