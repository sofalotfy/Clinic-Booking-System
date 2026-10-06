<?php
namespace App\Enums;


enum AdminPermissionsEnum: string {
    case VIEW_DOCTORS = 'view all doctors';
    case VIEW_SINGLE_DOCTOR = 'view single doctor';
    case CREATE_DOCTOR = 'create doctor';
    case UPDATE_DOCTOR = 'update doctor';
    case DELETE_DOCTOR = 'delete doctor';
}