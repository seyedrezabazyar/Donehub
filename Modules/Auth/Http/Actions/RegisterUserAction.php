<?php

namespace Modules\Auth\Http\Actions;

use Illuminate\Http\JsonResponse;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Auth\Services\AuthService;

class RegisterUserAction
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
        ], 201);
    }
}
