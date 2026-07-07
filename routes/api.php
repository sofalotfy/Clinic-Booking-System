<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\API\PatientController;

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout'])->name('logout');
    Route::get('/me', [UserController::class, 'me'])->name('me');
    Route::post('/edit-profile', [UserController::class, 'editProfile'])->name('edit-profile');
    Route::post('/delete-user', [UserController::class, 'deleteUser'])->name('delete-user');

    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    // Route::get('/tasks', [TaskController::class, 'index']);      // list
    // Route::post('/tasks', [TaskController::class, 'store']);     // create
    // Route::get('/tasks/form', [TaskController::class, 'form']); // form
    // Route::put('/tasks/{task}', [TaskController::class, 'update']); // update
    // Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // delete

});

Route::get('/test', [TestController::class, 'test']);
