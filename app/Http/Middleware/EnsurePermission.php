<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard for the manual RBAC system. Usage:
 *   Route::middleware('permission:products.view')->...
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        abort_unless($user, 403, 'Not authenticated');
        if (! $user->hasPermission($permission)) {
            abort(403, "Missing permission: $permission");
        }

        return $next($request);
    }
}
