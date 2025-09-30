<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        Log::info('API Request', [
            'user_id'   => optional($request->user())->id,
            'endpoint'  => $request->method().' '.$request->path(),
            'timestamp' => now()->toDateTimeString(),
            'ip'        => $request->ip(),
        ]);

        return $response;
    }
}
