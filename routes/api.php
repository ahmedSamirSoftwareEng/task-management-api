<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // tasks routes
    Route::get('tasks', [TaskController::class, 'index'])
        ->middleware('permission:view tasks');

    Route::get('tasks/{task}', [TaskController::class, 'show'])
        ->middleware('permission:view tasks');

    Route::put('tasks/{task}', [TaskController::class, 'update'])
        ->middleware('permission:edit tasks');

    Route::post('tasks', [TaskController::class, 'store'])
        ->middleware('permission:create tasks');

    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
        ->middleware('permission:delete tasks');
});
