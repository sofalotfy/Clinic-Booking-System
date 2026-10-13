<?php
namespace App\Enums;


enum AdminPermissionsEnum: string {
    case VIEW_DOCTORS = 'view all doctors';
    case VIEW_SINGLE_DOCTOR = 'view single doctor';
    case CREATE_DOCTOR = 'create doctor';
    case UPDATE_DOCTOR = 'update doctor';
    case DELETE_DOCTOR = 'delete doctor';

    case VIEW_ALL_ADMINS = 'view all admins';
    case VIEW_SINGLE_ADMIN = 'view single admin';
    case CREATE_ADMIN = 'create admin';
    case UPDATE_ADMIN = 'update admin';
    case DELETE_ADMIN = 'delete admin';

    case VIEW_ALL_ROLES = 'view all roles';
    case VIEW_SINGLE_ROLE = 'view single role';
    case CREATE_ROLE = 'create role';
    case UPDATE_ROLE = 'update role';
    case DELETE_ROLE = 'delete role';

    case VIEW_ALL_PERMISSIONS = 'view all permissions';

    public function group(): string
    {
        return match ($this) {
            self::VIEW_DOCTORS,
            self::VIEW_SINGLE_DOCTOR,
            self::CREATE_DOCTOR,
            self::UPDATE_DOCTOR,
            self::DELETE_DOCTOR => 'Doctors',

            self::VIEW_ALL_ADMINS,
            self::VIEW_SINGLE_ADMIN,
            self::CREATE_ADMIN,
            self::UPDATE_ADMIN,
            self::DELETE_ADMIN => 'Admins',

            self::VIEW_ALL_ROLES,
            self::VIEW_SINGLE_ROLE,
            self::CREATE_ROLE,
            self::UPDATE_ROLE,
            self::DELETE_ROLE => 'Roles',

            self::VIEW_ALL_PERMISSIONS => 'Permissions',
        };
    }
}
