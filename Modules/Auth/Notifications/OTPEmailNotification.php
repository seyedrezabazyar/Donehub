<?php

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OTPEmailNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $appName = config('app.name', 'Laravel');

        if ($this->purpose === 'registration') {
            return (new MailMessage)
                ->subject("Welcome to {$appName} - Verify Your Email")
                ->greeting('Welcome!')
                ->line('Thank you for registering. Please use the verification code below to complete your registration:')
                ->line('')
                ->line("**{$this->otp}**")
                ->line('')
                ->line('This code will expire in 5 minutes.')
                ->line('If you did not create an account, no further action is required.');
        }

        return (new MailMessage)
            ->subject('Your Verification Code')
            ->greeting('Hello!')
            ->line('You have requested a verification code to access your account.')
            ->line('')
            ->line("**Your verification code is: {$this->otp}**")
            ->line('')
            ->line('This code will expire in 5 minutes.')
            ->line('For security reasons, do not share this code with anyone.')
            ->line('If you did not request this code, please ignore this email.');
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
