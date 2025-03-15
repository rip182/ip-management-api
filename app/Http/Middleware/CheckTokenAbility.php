<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class CheckTokenAbility
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $requiredAbility): Response
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token && $request->hasCookie('refresh_token')) {
                $token = $request->cookie('refresh_token');
                JWTAuth::setToken($token);
            }

            if (!$token) {
                return response()->json(['error' => 'Token not provided'], 401);
            }
            $payload = JWTAuth::getPayload($token);

            $abilities = $payload->get('abilities', []);
            if ($requiredAbility != $abilities) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        } catch (JWTException  $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
