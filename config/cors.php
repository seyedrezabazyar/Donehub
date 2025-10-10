<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | این تنظیمات مشخص می‌کند چه دامنه‌هایی می‌توانند به API شما درخواست بدهند.
    | در حالت توسعه (localhost) بهتر است گزینه‌ها را باز بگذارید تا خطای CORS نگیرید.
    |
    */

    'paths' => [
        'api/*',
        'convert-date',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'], // همه متدها (GET, POST, PUT, DELETE...)

    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // همه هدرها مجازن

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // اگر از کوکی یا Auth استفاده می‌کنی بذار true
];
