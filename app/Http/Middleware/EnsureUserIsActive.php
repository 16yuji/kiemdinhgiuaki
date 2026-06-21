<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->status !== 'active') {
            if (!$request->routeIs('account.locked') && !$request->routeIs('logout')) {
                return redirect()->route('account.locked');
            }
        }

        return $next($request);
    }
}