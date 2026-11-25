<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Única ruta pública.
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');

// Todo lo demás exige token de Passport y cuenta activa.
Route::middleware(['auth:api', 'active'])->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::middleware('permission:competitions.view')->group(function (): void {
        Route::get('competitions', [CompetitionController::class, 'index']);
        Route::get('competitions/{competition_id}', [CompetitionController::class, 'show'])->whereNumber('competition_id');
        Route::get('competitions/{competition_id}/import-status', [CompetitionController::class, 'importStatus'])->whereNumber('competition_id');
    });

    Route::middleware('permission:teams.view')->group(function (): void {
        Route::get('team', [TeamController::class, 'index']);
        Route::get('team/{team_id}', [TeamController::class, 'show'])->whereNumber('team_id');
    });

    Route::middleware('permission:players.view')->group(function (): void {
        Route::get('players', [PlayerController::class, 'index']);
        Route::get('players/{player_id}', [PlayerController::class, 'show'])->whereNumber('player_id');
    });

    // Administración de usuarios, roles y permisos (decidido en el servidor, no solo en la SPA).
    Route::middleware('permission:users.manage')->group(function (): void {
        Route::get('users', [UserController::class, 'index']);
        Route::post('users', [UserController::class, 'store']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::patch('users/{user}/activate', [UserController::class, 'activate']);
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate']);
    });

    // Quien administra usuarios necesita ver roles/permisos para asignarlos.
    Route::middleware('permission:users.manage|roles.manage')->group(function (): void {
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('permissions', [RoleController::class, 'permissions']);
    });

    Route::middleware('permission:roles.manage')->group(function (): void {
        Route::post('roles', [RoleController::class, 'store']);
        Route::put('roles/{role}', [RoleController::class, 'update']);
        Route::delete('roles/{role}', [RoleController::class, 'destroy']);
    });
});
