<?php

namespace App\Services\Permissions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class ListPermissions
{
    public static function execute($user, $filters = null)
    {
        $builder = Permission::when($filters, fn ($builder) => self::filter($builder, $filters))
            ->orderBy('id', 'asc');

        return self::withAuthorizedCount($builder, $user);
    }

    /**
     * Eagerly attach, for each permission row, how many people hold it. The
     * count comes from the correlated subquery and is exposed as an
     * "authorized" column while every original permission field is kept.
     */
    private static function withAuthorizedCount($builder, $user)
    {
        return $builder->addSelect(['authorized' => self::authorizedSubquery($user)]);
    }

    private static function authorizedSubquery($user)
    {
        return self::roleAssignments($user)
            ->selectRaw('count(distinct model_has_roles.model_id)')
            ->whereColumn('role_has_permissions.permission_id', 'permissions.id');
    }

    /**
     * Role assignments only (direct per-user grants are ignored). When a user
     * is given, roles are scoped to that user's realm: platform (no doctor_id)
     * roles for an admin, the user's own clinic roles otherwise.
     */
    private static function roleAssignments($user = null)
    {
        $query = DB::table('model_has_roles')
            ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'model_has_roles.role_id')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_type', User::class);

        if ($user && $user->isAdmin()) {
            $query->whereNull('roles.doctor_id');
        } elseif ($user) {
            $query->where('roles.doctor_id', $user->clinicDoctorId());
        }

        return $query;
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['type']), function ($builder) use ($filters) {
                $builder->where('type', $filters['type']);
            });
    }
}
