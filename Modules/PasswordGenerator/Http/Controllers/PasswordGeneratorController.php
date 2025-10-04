<?php

namespace Modules\PasswordGenerator\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\PasswordGenerator\Http\Requests\GeneratePasswordRequest;
use Modules\PasswordGenerator\Services\PasswordGeneratorService;

class PasswordGeneratorController extends Controller
{
    protected $passwordService;

    public function __construct(PasswordGeneratorService $passwordService)
    {
        $this->passwordService = $passwordService;
    }

    /**
     * تولید رمز عبور تصادفی
     *
     * @param GeneratePasswordRequest $request
     * @return JsonResponse
     */
    public function generate(GeneratePasswordRequest $request): JsonResponse
    {
        try {
            $password = $this->passwordService->generate(
                $request->validated('length'),
                $request->validated('include_numbers', false),
                $request->validated('include_lowercase', false),
                $request->validated('include_uppercase', false),
                $request->validated('include_symbols', false)
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'password' => $password,
                    'length' => strlen($password)
                ],
                'message' => 'Password generated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}