<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use App\Models\InternetProtocolAddress;

class RestricUserRoleIpEdit
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->route('internet_protocol_address');

        Gate::authorize('update', $ip);

        return $next($request);
    }
}
