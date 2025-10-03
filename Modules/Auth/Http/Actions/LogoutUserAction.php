<?php

namespace Modules\Auth\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuthService;

class LogoutUserAction
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }
}
