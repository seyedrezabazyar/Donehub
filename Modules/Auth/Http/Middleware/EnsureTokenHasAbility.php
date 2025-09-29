<?php

namespace Modules\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenHasAbility
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     * @param string ...$abilities
     */
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Get current access token
        $token = $request->user()->currentAccessToken();
        
        if (!$token) {
            return response()->json([
                'message' => 'Invalid token'
            ], 401);
        }
        
        // Check each required ability
        foreach ($abilities as $ability) {
            if (!$token->can($ability)) {
                return response()->json([
                    'message' => 'Token does not have required ability: ' . $ability,
                    'required_ability' => $ability,
                    'token_abilities' => $token->abilities
                ], 403);
            }
        }
        
        return $next($request);
    }
}