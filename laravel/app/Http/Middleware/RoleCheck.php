<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleCheck
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Forbidden: requires role ' . $role,
            ], 403);
        }

        return $next($request);
    }
}
