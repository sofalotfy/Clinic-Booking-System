<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\PatientController;
use App\Http\Controllers\API\AppointmentController;

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/me', [UserController::class, 'me'])->name('me');
    Route::post('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('delete-user');


    Route::get('/patients', [PatientController::class, 'index'])->name('patients');


    Route::post('/appointment', [AppointmentController::class, 'store'])->name('appointment');
    


    Route::post('/flag', [FlagController::class, 'store'])->name('flag');
});

Route::get('/test', [TestController::class, 'test']);
