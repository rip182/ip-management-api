<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\TokenAbility;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        $user = User::where('email',  $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => ['Username or password incorrect'],
            ]);
        }

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.access_token_expiration')))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.refresh_access_expiration')))->plainTextToken;

        \OwenIt\Auditing\Models\Audit::create([
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'event' => 'login',
            'auditable_type' => get_class($user),
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [
                'logged_in_at' => now()->toDateTimeString(),
                'guard' => 'sanctum',
                'remember' => false,
            ],
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => 'auth,login',
            'created_at' => now(),
        ]);

        return response()->json([
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(
            [
                'status' => 'success',
                'message' => 'User logged out successfully'
            ]
        );
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.access_token_expiration')))->plainTextToken;

        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.refresh_access_expiration')))->plainTextToken;

        return response()->json(['message' => "Token Generate Success", 'token' => $accessToken, 'refreshToken' => $refreshToken]);
    }
}
