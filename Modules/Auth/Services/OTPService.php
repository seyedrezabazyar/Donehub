<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\{Cache, Hash, Log, Http};
use Modules\Auth\Models\User;
use Modules\Auth\Notifications\{OTPEmailNotification, OTPSMSNotification};

class OTPService
{
    private const OTP_EXPIRY = 300; // 5 minutes
    private const MAX_ATTEMPTS = 3;
    private const RATE_LIMIT = 3;
    private const RATE_WINDOW = 600; // 10 minutes

    public function __construct(private PhoneService $phoneService) {}

    public function send(string $identifier, string $type = 'auto', string $purpose = 'login'): array
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $detectedType = filter_var($normalized, FILTER_VALIDATE_EMAIL) ? 'email' : 'sms';
        $type = $type === 'auto' ? $detectedType : $type;

        $this->checkRateLimit($normalized);

        if ($existing = $this->getOtpData($normalized)) {
            $remaining = $this->getRemainingTime($normalized);
            if ($remaining > 240) {
                return [
                    'success' => true,
                    'message' => 'کد قبلی هنوز معتبر است',
                    'expires_in' => $remaining,
                    'can_resend_in' => $remaining - 240
                ];
            }
        }

        $otp = app()->environment(['local', 'testing']) ? '123456' : str_pad(random_int(100000, 999999), 6, '0');
        $this->storeOtp($normalized, $otp, $type, $purpose);

        try {
            $this->sendOtpToUser($normalized, $otp, $type, $purpose);
        } catch (\Exception $e) {
            $this->clearOtp($normalized);
            throw new \Exception('خطا در ارسال کد: ' . $e->getMessage());
        }

        $this->updateRateLimit($normalized);

        $response = [
            'success' => true,
            'message' => $type === 'email' ? 'کد به ایمیل ارسال شد' : 'کد به تلفن ارسال شد',
            'expires_in' => self::OTP_EXPIRY,
            'identifier' => $normalized,
            'type' => $type
        ];

        if (app()->environment(['local', 'testing'])) {
            $response['debug_code'] = $otp;
        }

        return $response;
    }

    public function verify(string $identifier, string $otp): bool
    {
        $normalized = $this->normalizeIdentifier($identifier);
        $otpData = $this->getOtpData($normalized);

        if (!$otpData || $otpData['attempts'] >= self::MAX_ATTEMPTS) {
            $this->clearOtp($normalized);
            return false;
        }

        if ($this->isExpired($otpData)) {
            $this->clearOtp($normalized);
            return false;
        }

        $isValid = Hash::check($otp, $otpData['code_hash']);

        // Development fallback
        if (!$isValid && app()->environment(['local', 'testing']) &&
            isset($otpData['debug_code']) && $otp === $otpData['debug_code']) {
            $isValid = true;
        }

        if (!$isValid) {
            $otpData['attempts']++;
            $this->updateOtpData($normalized, $otpData);
            return false;
        }

        $this->clearOtp($normalized);
        $this->clearRateLimit($normalized);
        return true;
    }

    public function exists(string $identifier): bool
    {
        $otpData = $this->getOtpData($this->normalizeIdentifier($identifier));
        return $otpData && !$this->isExpired($otpData);
    }

    public function getRemainingTime(string $identifier): int
    {
        $otpData = $this->getOtpData($this->normalizeIdentifier($identifier));
        if (!$otpData) return 0;

        return max(0, self::OTP_EXPIRY - (time() - $otpData['created_at']));
    }

    private function normalizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);
        return filter_var($identifier, FILTER_VALIDATE_EMAIL) ?
            strtolower($identifier) :
            $this->phoneService->normalize($identifier);
    }

    private function storeOtp(string $identifier, string $otp, string $type, string $purpose): void
    {
        $key = 'otp:' . hash('sha256', $identifier);
        $data = [
            'code_hash' => Hash::make($otp),
            'attempts' => 0,
            'created_at' => time(),
            'type' => $type,
            'purpose' => $purpose
        ];

        if (app()->environment(['local', 'testing'])) {
            $data['debug_code'] = $otp;
        }

        if (!Cache::put($key, $data, self::OTP_EXPIRY)) {
            throw new \Exception('خطا در ذخیره کد');
        }
    }

    private function getOtpData(string $identifier): ?array
    {
        return Cache::get('otp:' . hash('sha256', $identifier));
    }

    private function updateOtpData(string $identifier, array $data): void
    {
        $key = 'otp:' . hash('sha256', $identifier);
        $remaining = $this->getRemainingTime($identifier);
        if ($remaining > 0) {
            Cache::put($key, $data, $remaining);
        }
    }

    private function clearOtp(string $identifier): void
    {
        Cache::forget('otp:' . hash('sha256', $identifier));
    }

    private function isExpired(array $otpData): bool
    {
        return (time() - $otpData['created_at']) > self::OTP_EXPIRY;
    }

    private function checkRateLimit(string $identifier): void
    {
        $key = 'rate:' . hash('sha256', $identifier);
        $attempts = (int) Cache::get($key, 0);

        if ($attempts >= self::RATE_LIMIT) {
            throw new \Exception('تعداد درخواست بیش از حد مجاز');
        }
    }

    private function updateRateLimit(string $identifier): void
    {
        $key = 'rate:' . hash('sha256', $identifier);
        $current = (int) Cache::get($key, 0);
        Cache::put($key, $current + 1, self::RATE_WINDOW);
    }

    private function clearRateLimit(string $identifier): void
    {
        Cache::forget('rate:' . hash('sha256', $identifier));
    }

    private function sendOtpToUser(string $identifier, string $otp, string $type, string $purpose): void
    {
        if ($type === 'email') {
            $this->sendEmail($identifier, $otp, $purpose);
        } else {
            $this->sendSms($identifier, $otp, $purpose);
        }
    }

    private function sendEmail(string $email, string $otp, string $purpose): void
    {
        if (app()->environment(['local', 'testing'])) {
            Log::info('📧 Email OTP', compact('email', 'otp', 'purpose'));
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->notify(new OTPEmailNotification($otp, $purpose));
        } else {
            \Notification::route('mail', $email)
                ->notify(new OTPEmailNotification($otp, $purpose));
        }
    }

    private function sendSms(string $phone, string $otp, string $purpose): void
    {
        if (app()->environment(['local', 'testing'])) {
            Log::info('📱 SMS OTP', compact('phone', 'otp', 'purpose'));
            return;
        }

        $provider = $this->phoneService->isIranian($phone) ? 'kavenegar' : 'twilio';
        $this->sendViaSmsProvider($phone, $otp, $provider, $purpose);
    }

    private function sendViaSmsProvider(string $phone, string $otp, string $provider, string $purpose): void
    {
        match($provider) {
            'kavenegar' => $this->sendViaKavenegar($phone, $otp),
            'twilio' => $this->sendViaTwilio($phone, $otp, $purpose),
            default => throw new \Exception('Unsupported SMS provider')
        };
    }

    private function sendViaKavenegar(string $phone, string $otp): void
    {
        $config = config('auth-module.sms.providers.kavenegar');
        if (!$config['enabled'] || !$config['api_key']) return;

        $response = Http::timeout(30)->post(
            "https://api.kavenegar.com/v1/{$config['api_key']}/verify/lookup.json",
            [
                'receptor' => $phone,
                'token' => $otp,
                'template' => $config['template']
            ]
        );

        if (!$response->successful()) {
            throw new \Exception('Kavenegar SMS failed');
        }
    }

    private function sendViaTwilio(string $phone, string $otp, string $purpose): void
    {
        $config = config('auth-module.sms.providers.twilio');
        if (!$config['enabled'] || !$config['sid']) return;

        $message = $purpose === 'registration' ?
            "Welcome! Your code: {$otp}" :
            "Your code: {$otp}";

        $response = Http::withBasicAuth($config['sid'], $config['token'])
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$config['sid']}/Messages.json", [
                'From' => $config['from'],
                'To' => $phone,
                'Body' => $message
            ]);

        if (!$response->successful()) {
            throw new \Exception('Twilio SMS failed');
        }
    }
}
