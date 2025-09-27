<?php

return [
    // Token Configuration
    'tokens' => [
        'access_token_lifetime' => (int) env('ACCESS_TOKEN_LIFETIME', 7200), // 2 hours
        'refresh_token_lifetime' => (int) env('REFRESH_TOKEN_LIFETIME', 604800), // 7 days
    ],

    // OTP Configuration
    'otp' => [
        'expiry' => env('OTP_EXPIRY', 300), // 5 minutes
        'max_attempts' => env('OTP_MAX_ATTEMPTS', 3),
        'length' => 6,
    ],

    // Security Configuration
    'security' => [
        'login_max_attempts' => env('LOGIN_MAX_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 15), // minutes
        'password_min_length' => 8,
    ],

    // SMS Providers Configuration
    'sms' => [
        'default' => env('SMS_PROVIDER', 'twilio'),

        'providers' => [
            'twilio' => [
                'enabled' => env('TWILIO_ENABLED', false),
                'sid' => env('TWILIO_SID'),
                'token' => env('TWILIO_AUTH_TOKEN'),
                'from' => env('TWILIO_FROM_NUMBER'),
            ],

            'kavenegar' => [
                'enabled' => env('KAVENEGAR_ENABLED', false),
                'api_key' => env('KAVENEGAR_API_KEY'),
                'sender' => env('KAVENEGAR_SENDER'),
                'template' => env('KAVENEGAR_TEMPLATE', 'verify'),
            ],
        ],
    ],

    // Rate Limiting Configuration
    'rate_limits' => [
        'login' => ['attempts' => 5, 'decay_minutes' => 1],
        'register' => ['attempts' => 3, 'decay_minutes' => 60],
        'otp_send' => ['attempts' => 1, 'decay_minutes' => 1],
        'otp_verify' => ['attempts' => 3, 'decay_minutes' => 1],
        'refresh' => ['attempts' => 10, 'decay_minutes' => 1],
    ],

    // Features Configuration
    'features' => [
        'registration' => env('AUTH_REGISTRATION_ENABLED', true),
        'otp_login' => env('AUTH_OTP_LOGIN_ENABLED', true),
        'password_login' => env('AUTH_PASSWORD_LOGIN_ENABLED', true),
        'phone_verification' => env('AUTH_PHONE_VERIFICATION_ENABLED', true),
    ],

    // Validation Rules
    'validation' => [
        'phone_regex' => '/^(\+?[1-9]\d{0,2})?[\s.-]?\(?\d{1,4}\)?[\s.-]?\d{1,4}[\s.-]?\d{1,9}$/',
        'iranian_phone_regex' => '/^(98|0)?9\d{9}$/',
    ],
];
