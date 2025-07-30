<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('api/v1/logs') || $request->is('api/v1/logs/*') || $request->is('logs')) {
            return $response;
        }

        Log::channel('activity')->info('User Request', [
            'user' => auth()->user()?->name ?? 'Guest',
            'user_id' => auth()->user()?->id ?? 'Guest',
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'request' => $request->except(['password']), // exclude sensitive
        ]);

        return $response;
    }
}
