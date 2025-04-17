<?php
namespace App\Http\Middleware;

use App\Helpers\ResponseFormatter;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            return ResponseFormatter::error(message: 'Unauthorized: permission denied ', code: 403);
        }

        return $next($request);
    }
}
