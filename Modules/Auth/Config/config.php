<?php

return [
    'token_name' => env('AUTH_TOKEN_NAME', 'auth_token'),

    'password_min_length' => env('AUTH_PASSWORD_MIN_LENGTH', 6),

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],

    'supports_credentials' => true,
];

