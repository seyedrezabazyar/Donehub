<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Auth\Http\Actions\LoginUserAction;
use Modules\Auth\Http\Actions\LogoutUserAction;
use Modules\Auth\Http\Actions\RegisterUserAction;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        return $action($request);
    }

    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        return $action($request);
    }

    public function logout(Request $request, LogoutUserAction $action): JsonResponse
    {
        return $action($request);
    }
}
