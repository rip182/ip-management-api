<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Enums\TokenAbility;
use App\Enums\Permission;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\InternetProtocolAddressController;
use App\Http\Controllers\DashboardController;


Route::middleware(['auth:api', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'user' => $user,
            'role' => $user->getRoleNames()->first()
        ]);
    });

    Route::apiResource('/internet-protocol-address', InternetProtocolAddressController::class)
        ->only(['index', 'show', 'store']);

    Route::put('/internet-protocol-address/{internet_protocol_address}', [InternetProtocolAddressController::class, 'update'])
        ->middleware(['check-user-can-ip-edit']);

    Route::delete('internet-protocol-address/{id}', [InternetProtocolAddressController::class, 'destroy'])
        ->middleware('permission:' . Permission::DELETE_IP->value);

    Route::apiResource('/audit', AuditController::class)
        ->only(['index', 'show'])
        ->middleware(['permission:' . Permission::READ_AUDIT->value]);

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('get-active-users', [DashboardController::class, 'getActiveUsers']);
    Route::get('stats', [DashboardController::class, 'getStats']);
});

Route::middleware(['ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])->group(function () {
    Route::get('/auth/refresh-token', [AuthController::class, 'refreshToken']);
});




Route::post('/login', [AuthController::class, 'login']);
