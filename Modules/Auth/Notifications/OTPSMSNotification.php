<?php
// File: Modules/Auth/Notifications/OTPSMSNotification.php

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OTPSMSNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $otp;
    private string $purpose;

    public function __construct(string $otp, string $purpose = 'login')
    {
        $this->otp = $otp;
        $this->purpose = $purpose;
    }

    public function via(mixed $notifiable): array
    {
        // Use custom SMS channel instead of Vonage
        return ['sms'];
    }

    /**
     * Get the SMS representation (handled by our OTPService).
     */
    public function toSms(mixed $notifiable): array
    {
        $message = $this->purpose === 'registration'
            ? "Welcome! Your verification code is: {$this->otp}"
            : "Your verification code is: {$this->otp}";

        return [
            'message' => $message . "\n\nValid for 5 minutes. Do not share this code.",
            'phone' => $notifiable->phone
        ];
    }

    public function toArray(mixed $notifiable): array
    {
        return [
            'otp' => $this->otp,
            'purpose' => $this->purpose,
            'expires_at' => now()->addMinutes(5)->toIso8601String()
        ];
    }
}
