<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    /**
     * Authorize an admin action against a platform (type = admin) permission.
     *
     * The permission is resolved by name *and* type so a clinic permission
     * that shares the same name (for example "update doctor") can never
     * satisfy an admin check. Access is then granted only when the admin
     * actually holds that permission, whether assigned directly or inherited
     * through one of their roles.
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasAdminPermission($permission),
            403,
            'You do not have permission to perform this action.'
        );

        return $next($request);
    }
}
