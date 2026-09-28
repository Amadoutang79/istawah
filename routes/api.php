
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::get('me',      [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Profil
    Route::get('profile', [UserController::class, 'profile']);

    // Users (ADMIN / MANAGER pour lecture selon policy)
    Route::apiResource('users', UserController::class);

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::patch('projects/{project}/archive', [ProjectController::class, 'archive']);

    // Members
    Route::get('projects/{project}/members',              [ProjectController::class, 'members']);
    Route::post('projects/{project}/members',             [ProjectController::class, 'assignMember']);
    Route::delete('projects/{project}/members/{user}',    [ProjectController::class, 'removeMember']);

    // Route sensible réservée ADMIN (exemple explicite demandé par le sujet)
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('admin/ping', fn () => response()->json(['message' => 'ADMIN OK']));
    });
});
