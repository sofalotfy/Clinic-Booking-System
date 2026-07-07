<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TestController;

Route::post('/login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/me', [UserController::class, 'me']);

    Route::get('/tasks', [TaskController::class, 'index']);      // list
    Route::post('/tasks', [TaskController::class, 'store']);     // create
    Route::get('/tasks/form', [TaskController::class, 'form']); // form
    Route::put('/tasks/{task}', [TaskController::class, 'update']); // update
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']); // delete

});

Route::get('/test', [TestController::class, 'test']);
