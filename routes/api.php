<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SubtaskController;
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
Route::post('tasks/{task}/labels', [TaskController::class, 'syncLabels'])->middleware('auth:sanctum')->name('tasks.syncLabels');

// Subtask Routes (nested under tasks)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('tasks/{task}/subtasks', [SubtaskController::class, 'index'])->name('subtasks.index');
    Route::post('tasks/{task}/subtasks', [SubtaskController::class, 'store'])->name('subtasks.store');
    Route::patch('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::delete('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');
    Route::post('tasks/{task}/subtasks/reorder', [SubtaskController::class, 'reorder'])->name('subtasks.reorder');
});

// Comment Routes (shallow nesting)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('tasks/{task}/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('tasks/{task}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

// Label Routes (nested under projects)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('projects/{project}/labels', [LabelController::class, 'index'])->name('labels.index');
    Route::post('projects/{project}/labels', [LabelController::class, 'store'])->name('labels.store');
    Route::put('projects/{project}/labels/{label}', [LabelController::class, 'update'])->name('labels.update');
    Route::delete('projects/{project}/labels/{label}', [LabelController::class, 'destroy'])->name('labels.destroy');
});

// Activity Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('tasks/{task}/activities', [ActivityController::class, 'index'])->name('activities.task');
    Route::get('projects/{project}/activities', [ActivityController::class, 'projectActivities'])->name('activities.project');
});
