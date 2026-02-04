<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');

    // Password reset routes
    Route::post('password/email', [PasswordResetController::class, 'sendReset'])->name('password.email');
    Route::post('password/reset', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class)->name('logout');
    });
});

// Dashboard Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
});

// Board Routes
Route::apiResource('projects.boards', BoardController::class)->middleware('auth:sanctum');

// Task Routes
Route::apiResource('tasks', TaskController::class)->middleware('auth:sanctum');
Route::post('tasks/{task}/move', [TaskController::class, 'move'])->middleware('auth:sanctum')->name('tasks.move');
