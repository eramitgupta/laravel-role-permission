<?php

namespace EragPermission\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
        public function handle(Request $request, Closure $next, ...$permissions): Response
        {
                if (! $request->user()) {
                    abort(403, 'Unauthorized action.');
                }

            if (! $request->user()->hasPermissions($permissions)) {
                  abort(  403, 'You do not have the required permission.');
            }

            return $next($request);
        }
}
