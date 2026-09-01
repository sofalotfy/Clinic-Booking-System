<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Authentications\CheckDoctorPermission;

class ClinicPermission
{
    public function handle(Request $request, Closure $next, string $permission, ?string $routeModel = null)
    {
        $model = null;

        if ($routeModel) {
            $model = $request->route($routeModel);
        }

        abort_unless(
            CheckDoctorPermission::execute($request->user(), $permission, $model),
            403,
            'You do not have permission to perform this action.'
        );

        return $next($request);
    }
}