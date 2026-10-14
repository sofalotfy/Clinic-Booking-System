<?php

namespace App\Services\Roles;

use App\Enums\PermissionsTypeEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class StoreRole
{
    public static function execute(User $user, string $roleName, array $permissions)
    {
        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'doctor_id' => $user->clinicDoctorId(),
        ]);

        $role->syncPermissions(self::clinicPermissionIds($permissions));

        return $role;
    }

    /**
     * A doctor-scoped role may never hold a platform (admin) permission.
     */
    public static function clinicPermissionIds(array $permissions)
    {
        return Permission::query()
            ->whereIn('id', array_map('intval', $permissions))
            ->where('type', '!=', PermissionsTypeEnum::ADMIN->value)
            ->pluck('id')
            ->all();
    }
}
