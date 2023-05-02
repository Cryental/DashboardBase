<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SocialSetPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()->password === null) {
            return redirect()->route('set-social-password');
        }

        return $next($request);
    }
}
