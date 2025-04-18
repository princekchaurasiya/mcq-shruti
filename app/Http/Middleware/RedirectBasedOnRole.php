<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && $request->route()->getName() === 'dashboard') {
            return redirect()->route(auth()->user()->getDashboardRoute());
        }

        return $next($request);
    }
} 