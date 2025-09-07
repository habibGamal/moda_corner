<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response;
        }
        return $response
            // ->header('Access-Control-Allow-Origin', env('CORS_ALLOW_ORIGIN', '*'))
            // ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            // ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ;
    }
}
