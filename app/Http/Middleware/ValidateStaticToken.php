<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateStaticToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staticToken = config('api.static_token');
        
        if (empty($staticToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Static token not configured'
            ], 500);
        }

        $token = $request->bearerToken();

        if (!$token || $token !== $staticToken) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid static token'
            ], 401);
        }

        return $next($request);
    }
}

