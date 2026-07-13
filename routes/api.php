<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\AppointmentController;
use App\Http\Controllers\API\FlagController;
use App\Http\Controllers\API\WhatsAppController;

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/me', [UserController::class, 'me'])->name('me');
    Route::post('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('delete-user');


    Route::get('/patients', [PatientController::class, 'index'])->name('patients');


    Route::post('/appointment', [AppointmentController::class, 'store'])->name('store-appointment');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('get-appointments');
    Route::post('/appointments', [AppointmentController::class, 'update'])->name('update-appointment');
    


    Route::post('/flag', [FlagController::class, 'store'])->name('flag');
    Route::post('/flag-patient', [FlagController::class, 'flagPatient'])->name('flag-patient');
});

Route::get('/test', [TestController::class, 'test']);
Route::post('/make-plan', [TestController::class, 'makePlan']);


Route::get('/webhook/whatsapp', [WhatsAppController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppController::class, 'receive']);