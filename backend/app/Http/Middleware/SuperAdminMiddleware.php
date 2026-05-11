<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'Veuillez vous connecter.');
        }

        if (!auth()->user()->is_super_admin) {
            auth()->logout();
            return redirect()->route('admin.login')->with('error', 'Accès non autorisé.');
        }

        return $next($request);
    }
}

