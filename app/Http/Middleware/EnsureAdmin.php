<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnsureAdmin
{
    // public function handle(Request $request, Closure $next)
    // {
        public function handle(Request $request, Closure $next)
        {
            Log::info('before admin check', [
                'role' => $request->user()?->role,
            ]);
        
            if ($request->user()?->role !== 'admin') {
                Log::info('blocked by admin middleware');
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        
            Log::info('passed admin middleware');
            return $next($request);
        // }
    }
}
