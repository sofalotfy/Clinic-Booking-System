<?php
namespace App\Enums;


enum AssistantPermissionsEnum: string {
    case VIEW_ALL_APPOINTMENTS = 'view all appointments';
    case VIEW_SINGLE_APPOINTMENT = 'view single appointment';
    case CREATE_APPOINTMENT = 'create appointment';
    case UPDATE_APPOINTMENT = 'update appointment';
    case DELETE_APPOINTMENT = 'delete appointment';

    case VIEW_ALL_PATIENTS = 'view all patients';
    case VIEW_SINGLE_PATIENT = 'view single patient';
    case CREATE_PATIENT = 'create patient';
    case UPDATE_PATIENT = 'update patient';
    case DELETE_PATIENT = 'delete patient';

    case VIEW_ALL_PLANS = 'view all plans';
    case VIEW_SINGLE_PLAN = 'view single plan';
    case CREATE_PLAN = 'create plan';
    case UPDATE_PLAN = 'update plan';
    case DELETE_PLAN = 'delete plan';

    case VIEW_ALL_ASSISTANTS = 'view all assistants';
    case VIEW_SINGLE_ASSISTANT = 'view single assistant';
    case CREATE_ASSISTANT = 'create assistant';
    case UPDATE_ASSISTANT = 'update assistant';
    case DELETE_ASSISTANT = 'delete assistant';

    case VIEW_ALL_PERMISSIONS = 'view all permissions';
    
    case VIEW_ALL_ROLES = 'view all roles';
    case VIEW_SINGLE_ROLE = 'view single role';
    case CREATE_ROLE = 'create role';
    case UPDATE_ROLE = 'update role';
    case DELETE_ROLE = 'delete role';
    case MANAGE_ASSISTANT_ROLES = 'manage assistant roles';

    case VIEW_ALL_FLAGS = 'view all flags';
    case VIEW_SINGLE_FLAG = 'view single flag';
    case CREATE_FLAG = 'create flag';
    case UPDATE_FLAG = 'update flag';
    case DELETE_FLAG = 'delete flag';

    case VIEW_ALL_NOTES = 'view all notes';
    case VIEW_SINGLE_NOTE = 'view single note';
    case CREATE_NOTE = 'create note';
    case UPDATE_NOTE = 'update note';
    case DELETE_NOTE = 'delete note';

    case VIEW_ALL_BLOCKED_PATIENTS = 'view all blocked patients';
    case VIEW_SINGLE_BLOCKED_PATIENT = 'view single blocked patient';
    case CREATE_BLOCKED_PATIENT = 'create blocked patient';
    case UPDATE_BLOCKED_PATIENT = 'update blocked patient';
    case DELETE_BLOCKED_PATIENT = 'delete blocked patient';
}
