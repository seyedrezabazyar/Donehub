<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Models\User;
use Modules\Auth\Services\{PhoneService, TokenService, OTPService};
use Modules\Auth\Actions\PasswordValidationRules;

class AuthController extends Controller
{
    use PasswordValidationRules;

    public function __construct(
        private PhoneService $phoneService,
        private TokenService $tokenService,
        private OTPService $otpService
    ) {}

    public function checkUser(Request $request): JsonResponse
    {
        try {
            $identifier = trim($request->input('identifier'));

            if (!$identifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'شناسه الزامی است'
                ], 400);
            }

            $normalized = $this->normalizeIdentifier($identifier);
            $user = $this->findUser($normalized);

            if ($user) {
                $hasPassword = !empty($user->password);

                return response()->json([
                    'success' => true,
                    'user_exists' => true,
                    'has_password' => $hasPassword,
                    'identifier' => $normalized,
                    'message' => $hasPassword ? 'کاربر دارای رمز عبور است' : 'کاربر بدون رمز عبور است'
                ]);
            }

            return response()->json([
                'success' => true,
                'user_exists' => false,
                'has_password' => false,
                'identifier' => $normalized,
                'message' => 'کاربر جدید'
            ]);

        } catch (\Exception $e) {
            Log::error('Check user error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در بررسی کاربر'
            ], 500);
        }
    }

    public function sendOtp(Request $request): JsonResponse
    {
        try {
            $identifier = trim($request->input('identifier'));

            if (!$identifier) {
                return response()->json([
                    'success' => false,
                    'message' => 'شناسه الزامی است'
                ], 400);
            }

            $rateLimitKey = 'otp:' . hash('sha256', $identifier);

            if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد درخواست بیش از حد مجاز. لطفا کمی صبر کنید'
                ], 429);
            }

            $result = $this->otpService->send($identifier);
            RateLimiter::hit($rateLimitKey, 600);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Send OTP error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در ارسال کد'
            ], 500);
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
                'otp' => 'required|string|digits:6',
                'name' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'اطلاعات ارسالی معتبر نیست',
                    'errors' => $validator->errors()
                ], 400);
            }

            $identifier = $this->normalizeIdentifier($request->identifier);

            // Verify OTP
            if (!$this->otpService->verify($identifier, $request->otp)) {
                return response()->json([
                    'success' => false,
                    'message' => 'کد تأیید نامعتبر یا منقضی شده است'
                ], 401);
            }

            // Find or create user
            $userData = $this->findOrCreateUser($identifier, $request->name);
            $user = $userData['user'];
            $isNewUser = $userData['is_new_user'];

            // Create tokens
            $tokens = $this->tokenService->createTokens($user);

            return response()->json([
                'success' => true,
                'message' => $isNewUser ? 'ثبت‌نام و ورود موفق' : 'ورود موفق',
                'is_new_user' => $isNewUser,
                'tokens' => $tokens,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'user' => $this->userResponse($user)
            ]);

        } catch (\Exception $e) {
            Log::error('Verify OTP error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'خطا در تأیید کد'
            ], 500);
        }
    }

    public function loginWithPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'اطلاعات ورود معتبر نیست',
                    'errors' => $validator->errors()
                ], 400);
            }

            $identifier = $this->normalizeIdentifier($request->identifier);

            $rateLimitKey = 'login:' . hash('sha256', $identifier);

            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'اطلاعات ورود معتبر نیست',
                    'errors' => $validator->errors()
                ], 400);
            }

            $identifier = $this->normalizeIdentifier($request->identifier);

            $rateLimitKey = 'login:' . hash('sha256', $identifier);

            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                return response()->json([
                    'success' => false,
                    'message' => 'تعداد تلاش بیش از حد مجاز'
                ], 429);
            }

            $user = $this->findUser($identifier);

            if (!$user) {
                RateLimiter::hit($rateLimitKey, 300);
                return response()->json([
                    'success' => false,
                    'message' => 'کاربری با این مشخصات یافت نشد'
                ], 401);
            }

            if ($user->isLocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حساب کاربری قفل شده است'
                ], 423);
            }

            if (!$user->password || !Hash::check($request->password, $user->password)) {
                RateLimiter::hit($rateLimitKey, 300);
                $user->recordFailedAttempt();

                return response()->json([
                    'success' => false,
                    'message' => 'رمز عبور اشتباه است'
                ], 401);
            }

            RateLimiter::clear($rateLimitKey);
            $user->clearFailedAttempts();

            $tokens = $this->tokenService->createTokens($user);

            return response()->json([
                'success' => true,
                'message' => 'ورود موفق',
                'tokens' => $tokens,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'user' => $this->userResponse($user)
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'خطا در ورود'
            ], 500);
        }
    }

    // سایر متدها...

    private function normalizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return strtolower($identifier);
        }

        try {
            return $this->phoneService->normalize($identifier);
        } catch (\Exception $e) {
            return strtolower($identifier);
        }
    }

    private function findUser(string $identifier): ?User
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return User::where('email', $identifier)->first();
        }

        return $this->phoneService->findUserByPhone($identifier);
    }

    private function findOrCreateUser(string $identifier, ?string $name = null): array
    {
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
        $user = $this->findUser($identifier);
        $isNewUser = false;

        if (!$user) {
            $userData = [
                'name' => $name ?: 'کاربر',
                'preferred_method' => 'otp',
                'last_login_at' => now()
            ];

            if ($isEmail) {
                $userData['email'] = $identifier;
                $userData['email_verified_at'] = now();
            } else {
                $userData['phone'] = $identifier;
                $userData['phone_verified_at'] = now();
            }

            $user = User::create($userData);
            $isNewUser = true;

            Log::info('New user created: ' . $user->id);
        } else {
            // Update name if provided for existing user without name
            if ($name && ($user->name === 'کاربر' || empty($user->name))) {
                $user->update(['name' => $name]);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Mark as verified
            if ($isEmail && !$user->email_verified_at) {
                $user->update(['email_verified_at' => now()]);
            } elseif (!$isEmail && !$user->phone_verified_at) {
                $user->update(['phone_verified_at' => now()]);
            }
        }

        return ['user' => $user, 'is_new_user' => $isNewUser];
    }

    private function userResponse(User $user): array
    {
        try {
            if (\Schema::hasTable('roles') && \Schema::hasTable('permissions')) {
                $user->load(['roles.permissions']);
            }

            $response = [
                'id' => $user->id,
                'name' => mb_convert_encoding($user->name, 'UTF-8', 'UTF-8'),
                'username' => $user->username ? mb_convert_encoding($user->username, 'UTF-8', 'UTF-8') : null,
                'email' => $user->email ? mb_convert_encoding($user->email, 'UTF-8', 'UTF-8') : null,
                'phone' => $user->phone ? mb_convert_encoding($user->phone, 'UTF-8', 'UTF-8') : null,
                'email_verified_at' => $user->email_verified_at,
                'phone_verified_at' => $user->phone_verified_at,
                'preferred_method' => $user->preferred_method,
                'avatar_url' => $user->avatar_url ? mb_convert_encoding($user->avatar_url, 'UTF-8', 'UTF-8') : null,
                'last_login_at' => $user->last_login_at,
                'is_admin' => (bool) $user->is_admin,
                'has_password' => !empty($user->password),
                'created_at' => $user->created_at,
                'province_id' => $user->province_id ?? null,
                'city_id' => $user->city_id ?? null,
                'address' => $user->address ? mb_convert_encoding($user->address, 'UTF-8', 'UTF-8') : null,
                'username_last_changed_at' => $user->username_last_changed_at,
                'days_until_username_change' => $user->getDaysUntilUsernameChange(),
                'roles' => [],
                'permissions' => []
            ];

            if ($user->relationLoaded('roles') && $user->roles) {
                $response['roles'] = $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => mb_convert_encoding($role->name, 'UTF-8', 'UTF-8'),
                        'display_name' => $role->display_name ? mb_convert_encoding($role->display_name, 'UTF-8', 'UTF-8') : mb_convert_encoding($role->name, 'UTF-8', 'UTF-8')
                    ];
                });

                if (method_exists($user, 'getAllPermissions')) {
                    $response['permissions'] = $user->getAllPermissions()->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => mb_convert_encoding($permission->name, 'UTF-8', 'UTF-8'),
                            'display_name' => $permission->display_name ? mb_convert_encoding($permission->display_name, 'UTF-8', 'UTF-8') : mb_convert_encoding($permission->name, 'UTF-8', 'UTF-8'),
                            'group' => $permission->group ? mb_convert_encoding($permission->group, 'UTF-8', 'UTF-8') : 'general'
                        ];
                    });
                }
            }

            return $response;
        } catch (\Exception $e) {
            \Log::error('Error in userResponse: ' . $e->getMessage());
            return [
                'id' => $user->id,
                'name' => mb_convert_encoding($user->name, 'UTF-8', 'UTF-8'),
                'email' => $user->email ? mb_convert_encoding($user->email, 'UTF-8', 'UTF-8') : null,
                'is_admin' => (bool) $user->is_admin,
                'roles' => [],
                'permissions' => []
            ];
        }
    }

    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربر یافت نشد'
                ], 401);
            }

            // Refresh user data from database
            $user->refresh();

            return response()->json([
                'success' => true,
                'user' => $this->userResponse($user)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in user method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت اطلاعات کاربر'
            ], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // جلوگیری از تغییر ایمیل یا شماره موبایل تایید شده
            if ($request->filled('email') && $request->email !== $user->email && $user->email_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما نمی‌توانید ایمیل تایید شده خود را تغییر دهید.',
                    'errors' => ['email' => ['شما نمی‌توانید ایمیل تایید شده خود را تغییر دهید.']]
                ], 422);
            }
            if ($request->filled('phone') && $request->phone !== $user->phone && $user->phone_verified_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما نمی‌توانید شماره موبایل تایید شده خود را تغییر دهید.',
                    'errors' => ['phone' => ['شما نمی‌توانید شماره موبایل تایید شده خود را تغییر دهید.']]
                ], 422);
            }

            // بررسی محدودیت زمانی برای تغییر نام کاربری
            if ($request->filled('username') && $request->username !== $user->username) {
                if (!$user->canChangeUsername()) {
                    $daysLeft = $user->getDaysUntilUsernameChange();
                    $message = "شما فقط هر ۳۶۵ روز یک بار می‌توانید نام کاربری خود را تغییر دهید. {$daysLeft} روز تا تغییر بعدی باقی مانده است.";
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'errors' => ['username' => [$message]]
                    ], 422);
                }
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
                'username' => 'nullable|string|max:50|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $user->id,
                'province_id' => 'nullable|integer',
                'city_id' => 'nullable|integer',
                'address' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'داده‌های ارسالی نامعتبر است',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['name', 'email', 'phone', 'username', 'province_id', 'city_id', 'address']);

            // حذف فیلدهای خالی و null
            $updateData = array_filter($updateData, function($value) {
                return $value !== null && $value !== '';
            });

            // Set the timestamp for the username change.
            // This is the counterpart to the canChangeUsername() check at the start of the method.
            if (isset($updateData['username']) && $updateData['username'] !== $user->username) {
                $updateData['username_last_changed_at'] = now();
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'اطلاعات با موفقیت به‌روزرسانی شد',
                'user' => $this->userResponse($user->fresh())
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی پروفایل'
            ], 500);
        }
    }

    public function sendEmailVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $email = $request->input('email');
        $otpKey = "profile_verify_email_{$user->id}";

        $this->otpService->send($email, $otpKey);

        return response()->json(['success' => true, 'message' => 'کد تایید به ایمیل شما ارسال شد.']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $email = $request->input('email');
        $otp = $request->input('otp');
        $otpKey = "profile_verify_email_{$user->id}";

        if ($this->otpService->verify($email, $otp, $otpKey)) {
            $user->update([
                'email' => $email,
                'email_verified_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'ایمیل با موفقیت تایید شد.', 'user' => $this->userResponse($user->fresh())]);
        }

        return response()->json(['success' => false, 'message' => 'کد تایید نامعتبر است.'], 422);
    }

    public function sendPhoneVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:20|unique:users,phone,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $phone = $request->input('phone');
        $otpKey = "profile_verify_phone_{$user->id}";

        $this->otpService->send($phone, $otpKey);

        return response()->json(['success' => true, 'message' => 'کد تایید به شماره موبایل شما ارسال شد.']);
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $phone = $request->input('phone');
        $otp = $request->input('otp');
        $otpKey = "profile_verify_phone_{$user->id}";

        if ($this->otpService->verify($phone, $otp, $otpKey)) {
            $user->update([
                'phone' => $phone,
                'phone_verified_at' => now(),
            ]);
            return response()->json(['success' => true, 'message' => 'شماره موبایل با موفقیت تایید شد.', 'user' => $this->userResponse($user->fresh())]);
        }

        return response()->json(['success' => false, 'message' => 'کد تایید نامعتبر است.'], 422);
    }

    public function setPassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!empty($user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'شما قبلاً رمز عبور تنظیم کرده‌اید. لطفاً از گزینه تغییر رمز عبور استفاده کنید.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'password' => $this->passwordRules(),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'رمز عبور با موفقیت تنظیم شد.', 'user' => $this->userResponse($user->fresh())]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (empty($user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'شما هنوز رمز عبور تنظیم نکرده‌اید. لطفاً ابتدا یک رمز عبور تنظیم کنید.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => $this->passwordRules(),
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'اطلاعات ارسالی معتبر نیست.', 'errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'رمز عبور فعلی اشتباه است.', 'errors' => ['current_password' => ['رمز عبور فعلی اشتباه است.']]], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['success' => true, 'message' => 'رمز عبور با موفقیت تغییر کرد.', 'user' => $this->userResponse($user->fresh())]);
    }
}
