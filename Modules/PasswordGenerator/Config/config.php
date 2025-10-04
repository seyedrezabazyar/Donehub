<?php

return [
    'name' => 'PasswordGenerator',
    
    // تنظیمات پیش‌فرض
    'defaults' => [
        'min_length' => 1,
        'max_length' => 50,
        'default_length' => 12,
    ],
    
    // کاراکترهای مجاز
    'characters' => [
        'numbers' => '0123456789',
        'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
        'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        'symbols' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
    ],
];