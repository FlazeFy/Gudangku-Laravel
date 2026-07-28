<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestingMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        if ($apiKey !== env('TESTING_API_KEY')) {
            return response()->json([
                'status' => 'failed',
                'message' => 'you need to include the api key',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}