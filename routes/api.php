<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
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

// Project Routes
Route::middleware('auth:sanctum')->group(function () {
    // Project CRUD
    Route::apiResource('projects', ProjectController::class);

    // Project custom actions
    Route::post('projects/{project}/archive', [ProjectController::class, 'archive'])->name('projects.archive');
    Route::post('projects/{project}/unarchive', [ProjectController::class, 'unarchive'])->name('projects.unarchive');
    Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');

    // Project member management
    Route::get('projects/{project}/members', [ProjectController::class, 'members'])->name('projects.members');
    Route::post('projects/{project}/members', [ProjectController::class, 'addMember'])->name('projects.addMember');
    Route::put('projects/{project}/members/{user}', [ProjectController::class, 'updateMember'])->name('projects.updateMember');
    Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember'])->name('projects.removeMember');
});

// Board Routes
Route::apiResource('projects.boards', BoardController::class)->middleware('auth:sanctum');

// Task Routes
Route::apiResource('tasks', TaskController::class)->middleware('auth:sanctum');
Route::post('tasks/{task}/move', [TaskController::class, 'move'])->middleware('auth:sanctum')->name('tasks.move');
