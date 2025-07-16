<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────  auth  ─────────────────────────
Route::prefix('auth')->group(function () {
    // POST /api/auth/login   → api.login
    Route::post('/login', [AuthController::class, 'login'])
        ->name('api.login');

    // POST /api/auth/logout  (потрібен токен)
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

// ─────────────────────────  захищений роут  ─────────────────────────
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
