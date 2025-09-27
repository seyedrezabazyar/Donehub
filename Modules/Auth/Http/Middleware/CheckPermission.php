<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();

        // مدیران به همه جا دسترسی دارند
        if ($user->isAdmin()) {
            return $next($request);
        }

        // بررسی دسترسی‌های مورد نیاز
        if (!$user->hasAnyPermission($permissions)) {
            return response()->json([
                'message' => 'شما دسترسی به این بخش را ندارید',
                'required_permissions' => $permissions
            ], 403);
        }

        return $next($request);
    }
}
