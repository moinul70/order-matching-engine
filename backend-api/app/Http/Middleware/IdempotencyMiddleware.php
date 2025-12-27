<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       // 1. Only process POST/PUT and ensure header exists
        if (!$request->isMethod('POST') && !$request->isMethod('PUT')) {
            return $next($request);
        }

        $key = $request->header('Idempotency-Key');

        if (!$key) {
            return response()->json(['error' => 'Idempotency-Key header is missing'], 400);
        }

        $cacheKey = "idempotency:$key";
        $lockKey = "idempotency_lock:$key";

        // 2. Check if we already have a successful response in Redis
        if ($cachedResponse = Cache::get($cacheKey)) {
            return response()->json($cachedResponse, 200);
        }

        // 3. Use a Redis Atomic Lock to prevent race conditions
        // We wait for up to 5 seconds to get the lock
        return Cache::lock($lockKey, 10)->block(5, function () use ($next, $request, $cacheKey) {
            
            // Double-check cache inside the lock (in case another thread just finished)
            if ($cachedResponse = Cache::get($cacheKey)) {
                return response()->json($cachedResponse, 200);
            }

            // 4. Proceed to the Controller
            $response = $next($request);

            // 5. Only cache successful responses (2xx)
            if ($response->isSuccessful()) {
                $data = json_decode($response->getContent(), true);
                // Store in Redis for 24 hours
                Cache::put($cacheKey, $data, now()->addHours(24));
            }

            return $response;
        });
    
    }
}
