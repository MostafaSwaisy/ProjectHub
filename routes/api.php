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
use App\Http\Controllers\TrashController;
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
    Route::post('tasks/{task}/subtasks/reorder', [SubtaskController::class, 'reorder'])->name('subtasks.reorder');
    Route::patch('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtasks.update');
    Route::delete('tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy'])->name('subtasks.destroy');
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

// Trash Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('projects/{project}/trash', [TrashController::class, 'index'])->name('trash.index');
    Route::post('projects/{project}/restore', [TrashController::class, 'restore'])->name('trash.restore');
    Route::post('projects/{project}/boards/{board}/restore', [TrashController::class, 'restore'])->name('boards.restore');
    Route::post('tasks/{task}/restore', [TrashController::class, 'restore'])->withTrashed()->name('tasks.restore');
    Route::post('tasks/{task}/subtasks/{subtask}/restore', [TrashController::class, 'restore'])->withTrashed()->name('subtasks.restore');
    Route::post('comments/{comment}/restore', [TrashController::class, 'restore'])->withTrashed()->name('comments.restore');

    // Force delete routes
    Route::delete('projects/{project}/force', [TrashController::class, 'forceDelete'])->name('trash.forceDelete');
    Route::delete('projects/{project}/boards/{board}/force', [TrashController::class, 'forceDelete'])->name('boards.forceDelete');
    Route::delete('tasks/{task}/force', [TrashController::class, 'forceDelete'])->withTrashed()->name('tasks.forceDelete');
    Route::delete('tasks/{task}/subtasks/{subtask}/force', [TrashController::class, 'forceDelete'])->withTrashed()->name('subtasks.forceDelete');
    Route::delete('comments/{comment}/force', [TrashController::class, 'forceDelete'])->withTrashed()->name('comments.forceDelete');
});

// User Management Routes (006-user-management feature)
Route::middleware('auth:sanctum')->group(function () {
    // Profile management (self-service)
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('profile/avatar', [\App\Http\Controllers\ProfileController::class, 'uploadAvatar'])->name('profile.avatar.store');
    Route::delete('profile/avatar', [\App\Http\Controllers\ProfileController::class, 'deleteAvatar'])->name('profile.avatar.destroy');
    Route::put('profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::put('profile/preferences', [\App\Http\Controllers\ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');

    // User management (admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [\App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::put('users/{user}', [\App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [\App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });

    // Project member management
    Route::get('projects/{project}/permissions', [ProjectController::class, 'permissions'])->name('projects.permissions');
    Route::get('projects/{project}/members/assignable', [ProjectController::class, 'assignableMembers'])->name('projects.members.assignable');

    // Invitation routes
    Route::get('projects/{project}/invitations', [\App\Http\Controllers\InvitationController::class, 'index'])->name('invitations.index');
    Route::post('projects/{project}/invitations', [\App\Http\Controllers\InvitationController::class, 'store'])->name('invitations.store');
    Route::post('projects/{project}/invitations/{invitation}/resend', [\App\Http\Controllers\InvitationController::class, 'resend'])->name('invitations.resend');
    Route::delete('projects/{project}/invitations/{invitation}', [\App\Http\Controllers\InvitationController::class, 'destroy'])->name('invitations.destroy');
    Route::post('invitations/{token}/accept', [\App\Http\Controllers\InvitationController::class, 'accept'])->name('invitations.accept');
    Route::post('invitations/{token}/decline', [\App\Http\Controllers\InvitationController::class, 'decline'])->name('invitations.decline');
    Route::get('invitations/pending', [\App\Http\Controllers\InvitationController::class, 'pending'])->name('invitations.pending');

    // Notification routes
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
});
