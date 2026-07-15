<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\FlagController;
use App\Http\Controllers\API\WhatsAppController;
use App\Http\Controllers\API\NoteController;

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/me', [UserController::class, 'me'])->name('me');
    Route::post('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('delete-user');


    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::get('/patients/{patient}', [PatientController::class, 'show'])->name('patients.show');


    Route::post('/appointment', [AppointmentController::class, 'store'])->name('store-appointment');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('get-appointments');
    Route::post('/appointments', [AppointmentController::class, 'update'])->name('update-appointment');
    


    Route::get('/flags', [FlagController::class, 'index'])->name('get-flags');
    Route::post('/flag', [FlagController::class, 'store'])->name('flag');
    Route::delete('/flag/{flag}', [FlagController::class, 'destroy'])->name('delete-flag');
    Route::post('/flag-patient', [FlagController::class, 'flagPatient'])->name('flag-patient');
    Route::post('/unflag-patient', [FlagController::class, 'unflagPatient'])->name('unflag-patient');

    Route::post('/note-patient', [NoteController::class, 'notePatient'])->name('note-patient');
    Route::put('/edit-note/{noteId}', [NoteController::class, 'editNote'])->name('edit-note');
    Route::delete('/delete-note/{noteId}', [NoteController::class, 'deleteNote'])->name('delete-note');
});

Route::get('/test', [TestController::class, 'test']);
Route::post('/make-plan', [TestController::class, 'makePlan']);


Route::get('/webhook/whatsapp', [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);