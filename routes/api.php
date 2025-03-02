<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Enums\TokenAbility;
use App\Enums\Permission;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\InternetProtocolAddressController;


Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/internet-protocol-address', InternetProtocolAddressController::class)
        ->only(['index', 'show', 'store'])
        ->middleware('permission:' . Permission::READ_IP->value . '|' . Permission::CREATE_IP->value, '|' . Permission::EDIT_IP->value);

    Route::put('/internet-protocol-address/{internet_protocol_address}', [InternetProtocolAddressController::class, 'update'])
        ->middleware(['check-user-can-ip-edit']);

    Route::delete('internet-protocol-address/{id}', [InternetProtocolAddressController::class, 'destroy'])
        ->middleware('permission:' . Permission::DELETE_IP->value);

    Route::apiResource('/audit', AuditController::class)
        ->only(['index', 'show'])
        ->middleware(['permission:' . Permission::READ_AUDIT->value]);
});

Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::get('/auth/refresh-token', [AuthController::class, 'refreshToken']);
});

Route::post('/login', [AuthController::class, 'login']);
