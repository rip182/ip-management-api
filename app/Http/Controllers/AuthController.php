<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\TokenAbility;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use OwenIt\Auditing\Models\Audit;

class AuthController extends Controller
{

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {
        $accessAbility = TokenAbility::ACCESS_API->value;
        $refreshAbility = TokenAbility::ISSUE_ACCESS_TOKEN->value;
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::claims(['abilities' => $accessAbility])->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = User::where('email',  $request->email)->first();

        $role = $user->getRoleNames()->first();
        $refreshToken = JWTAuth::claims([
            'refresh' => true,
            'abilities' => $refreshAbility
        ])->fromUser($user);

        Audit::create([
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

        $response =  response()->json([
            'accessToken' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user,
            'role' => $role,
        ]);

        return $response->withCookie(
            cookie(
                'refresh_token',
                $refreshToken,
                60 * 24 * 7,
                '/',
                null,
                config('app.env') === 'production',
                true,
                false,
                'Strict'
            )
        );
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            $request->user();
            $user = $request->user();
            Audit::create([
                'user_type' => get_class($user),
                'user_id' => $user->id,
                'event' => 'logout',
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
                'tags' => 'auth,logout',
                'created_at' => now(),
            ]);
            return response()->json(
                [
                    'status' => 'success',
                    'message' => 'User logged out successfully'
                ]
            )->withCookie(cookie()->forget('refresh_token'));
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function refreshToken(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');
        // dd($refreshToken, 'refresh');
        if (!$refreshToken) {
            return response()->json(['error' => 'Refresh token missing'], 401);
        }

        try {
            JWTAuth::setToken($refreshToken);
            if (!JWTAuth::payload()->get('refresh')) {
                return response()->json(['error' => 'Invalid refresh token'], 401);
            }
            // dd($refreshToken, 'refresh-fail');

            $user = JWTAuth::toUser($refreshToken);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $newAccessToken = JWTAuth::claims(['abilities' => 'access-api'])->fromUser($user);

            return response()->json(['accessToken' => $newAccessToken])->withCookie(
                cookie(
                    'refresh_token',
                    $refreshToken,
                    60 * 24 * 7,
                    '/',
                    null,
                    config('app.env') === 'production',
                    true,
                    false,
                    'Strict'
                )
            );;
        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Refresh token expired'], 403);
        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Refresh token invalid'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Refresh failed', 'message' => $e->getMessage()], 500);
        }
    }
}
