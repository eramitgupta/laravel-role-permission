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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role = null, $permission=null): Response
    {
        // Check if the user is authenticated
        if (!$request->user()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$request->user()->hasRole($role)) {
            abort(404, 'Unauthorized action.');
        }
        if ($permission !== null && !$request->user()->can($permission)) {
            abort(404, 'Unauthorized action.');
        }

        return $next($request);
    }
}
