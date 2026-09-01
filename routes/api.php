<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AssistantController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\PatientBlockController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\TemplatePlanController;
use App\Http\Controllers\API\DayController;
use App\Http\Controllers\API\FlagController;
use App\Http\Controllers\API\NoteController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\WhatsAppController;

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/me', [UserController::class, 'me'])->name('me');
    Route::post('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('delete-user');


    Route::get('/assistants', [AssistantController::class, 'index']);
    Route::get('/assistants/{assistant}', [AssistantController::class, 'show']);
    Route::post('/assistants', [AssistantController::class, 'store']);
    Route::post('/assistants/{assistant}', [AssistantController::class, 'update']);
    Route::delete('/assistants/{assistant}', [AssistantController::class, 'destroy']);
    Route::post('/assistants/{assistant}/roles/{role}', [AssistantController::class, 'assignRole']);
    Route::delete('/assistants/{assistant}/roles/{role}', [AssistantController::class, 'removeRole']);


    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');
    Route::post('/patients/{patient}/flags/{flag}', [PatientController::class, 'flagPatient'])->name('patients.flagPatient');//added
    Route::post('/patients/flags/{flag}', [PatientController::class, 'bulkFlag'])->name('patients.bulkFlag');//added replaces /flag-patient
    Route::delete('/patients/{patient}/flags/{flag}', [PatientController::class, 'unflagPatient'])->name('patients.unflagPatient');//added replaces /unflag-patient
    Route::post('/patients/{patient}/note', [PatientController::class, 'notePatient'])->name('patients.notePatient');//added replaces /note-patient

    Route::get('/patients-block', [PatientBlockController::class, 'index']);
    Route::post('/patients-block/{patient}', [PatientBlockController::class, 'store']);
    Route::delete('/patients-block/{patient}', [PatientBlockController::class, 'destroy']);


    Route::post('/appointments', [AppointmentController::class, 'store'])->name('store-appointment');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('update-appointment');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('get-appointments');
    Route::put('/appointments', [AppointmentController::class, 'bulkUpdate'])->name('bulk-update-appointment');


    Route::post('/template-plan', [TemplatePlanController::class, 'store'])->name('create-template');
    Route::get('/template-plans', [TemplatePlanController::class, 'index'])->name('get-template-plans');
    Route::get('/template-plan/{templatePlan}', [TemplatePlanController::class, 'show'])->name('show-template');
    Route::post('/template-plan/{templatePlan}', [TemplatePlanController::class, 'update'])->name('update-template');
    Route::delete('/template-plan/{templatePlan}', [TemplatePlanController::class, 'destroy'])->name('delete-template');
    Route::post('/template-plan/{templatePlan}/check', [TemplatePlanController::class, 'checkRePlan'])->name('checkRePlan-template'); 
    Route::post('/template-plan/{templatePlan}/activate', [TemplatePlanController::class, 'activate'])->name('activate-template'); 


    Route::get('/day/appointments', [DayController::class, 'dayAppointments'])->name('day-appointments');
    Route::post('/days/{day}/update', [DayController::class, 'update'])->name('update');
    Route::get('/days', [DayController::class, 'index'])->name('days');
    Route::get('/days/appointments', [DayController::class, 'mapAppointments'])->name('daysAppointments');


    Route::get('/flags', [FlagController::class, 'index'])->name('get-flags');
    Route::post('/flag', [FlagController::class, 'store'])->name('flag');
    Route::put('/flag/{flag}', [FlagController::class, 'update'])->name('update-flag');
    Route::delete('/flag/{flag}', [FlagController::class, 'destroy'])->name('delete-flag');


    Route::post('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');//added replaces /edit-note/{noteId}
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');//added replaces /delete-note/{noteId}
    
    Route::get('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::post('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');


    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions');
    Route::get('/permissions/assistants', [PermissionController::class, 'listAssistantPermissions'])->name('assistant-permissions');
    Route::get('/my-permissions', [PermissionController::class, 'GetUserPermissions'])->name('my-permissions');
    

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/historical', [NotificationController::class, 'historical']);
    Route::post('/notifications/view-bulk', [NotificationController::class, 'viewBulk']);
    Route::post('/notifications/{notification}', [NotificationController::class, 'view']);


    Route::get('/test', [TestController::class, 'test']);
});

Route::get('/webhook/whatsapp', [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);