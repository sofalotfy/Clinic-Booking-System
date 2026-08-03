<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Authentications\CheckClinicPermission;

class ClinicPermission
{
    public function handle(Request $request, Closure $next, string $permission, ?string $routeModel = null)
    {
        $model = null;

        if ($routeModel) {
            $model = $request->route($routeModel);
        }

        abort_if(
            ! CheckClinicPermission::execute(
                $request->user(),
                $permission,
                $model
            ),
            403
        );

        return $next($request);
    }
}