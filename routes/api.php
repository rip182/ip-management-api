<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Enums\TokenAbility;
use App\Enums\Permission;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\InternetProtocolAddressController;


Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('/internet-protocol-address', InternetProtocolAddressController::class);
    Route::apiResource('/audit', AuditController::class)->only(['index', 'show'])->middleware(['permission:read audit']);
    Route::apiResource('/audit', AuditController::class)->only(['index', 'show'])->middleware(['permission:' . Permission::READ_AUDIT->value]);
    Route::apiResource('/audit', AuditController::class)->only(['update', 'delete'])->middleware(['permission:edit audit | delete audit']);;
});
// ->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::get('/auth/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::post('/login', [AuthController::class, 'login']);
