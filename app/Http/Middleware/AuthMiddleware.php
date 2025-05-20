<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Helpers\ResponseFormatterController;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Http::withToken($request->bearerToken())
            ->get(env('USER_SERVICE_URL') . '/auth/me');

        if($user->failed())
        {
            return ResponseFormatterController::error('Unauthorized', 401);
        }

        return $next($request);
    }
}
