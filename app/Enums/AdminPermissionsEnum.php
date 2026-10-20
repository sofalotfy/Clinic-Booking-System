<?php
namespace App\Enums;


enum AdminPermissionsEnum: string {
    case VIEW_DOCTORS = 'view all doctors';
    case VIEW_SINGLE_DOCTOR = 'view single doctor';
    case CREATE_DOCTOR = 'create doctor';
    case UPDATE_DOCTOR = 'update doctor';
    case SUSPEND_DOCTOR = 'suspend doctor';
    case REINSTATE_DOCTOR = 'reinstate doctor';

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

    case VIEW_ALL_LABS = 'view all labs';
    case VIEW_SINGLE_LAB = 'view single lab';
    case CREATE_LAB = 'create lab';
    case UPDATE_LAB = 'update lab';
    case DELETE_LAB = 'delete lab';

    case VIEW_ALL_SCANS = 'view all scans';
    case VIEW_SINGLE_SCAN = 'view single scan';
    case CREATE_SCAN = 'create scan';
    case UPDATE_SCAN = 'update scan';
    case DELETE_SCAN = 'delete scan';

    case VIEW_ALL_MEDICINES = 'view all medicines';
    case VIEW_SINGLE_MEDICINE = 'view single medicine';
    case CREATE_MEDICINE = 'create medicine';
    case UPDATE_MEDICINE = 'update medicine';
    case DELETE_MEDICINE = 'delete medicine';
    case IMPORT_MEDICINES = 'import medicines';

    public function group(): string
    {
        return match ($this) {
            self::VIEW_DOCTORS,
            self::VIEW_SINGLE_DOCTOR,
            self::CREATE_DOCTOR,
            self::UPDATE_DOCTOR,
            self::SUSPEND_DOCTOR,
            self::REINSTATE_DOCTOR => 'Doctors',

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

            self::VIEW_ALL_LABS,
            self::VIEW_SINGLE_LAB,
            self::CREATE_LAB,
            self::UPDATE_LAB,
            self::DELETE_LAB => 'Labs',

            self::VIEW_ALL_SCANS,
            self::VIEW_SINGLE_SCAN,
            self::CREATE_SCAN,
            self::UPDATE_SCAN,
            self::DELETE_SCAN => 'Scans',

            self::VIEW_ALL_MEDICINES,
            self::VIEW_SINGLE_MEDICINE,
            self::CREATE_MEDICINE,
            self::UPDATE_MEDICINE,
            self::DELETE_MEDICINE,
            self::IMPORT_MEDICINES => 'Medicines',
        };
    }
}
