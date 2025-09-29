<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();

        // مدیران به همه جا دسترسی دارند
        if ($user->isAdmin()) {
            return $next($request);
        }

        // بررسی نقش‌های مورد نیاز
        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'message' => 'شما دسترسی به این بخش را ندارید',
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}
