<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Enums\TokenAbility;

Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::get('/auth/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::post('/login', [AuthController::class, 'login']);
