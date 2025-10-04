<?php

namespace Modules\PasswordGenerator\Services;

class PasswordGeneratorService
{
    private $numbers;
    private $lowercase;
    private $uppercase;
    private $symbols;

    public function __construct()
    {
        // خواندن کاراکترها از config
        $config = config('passwordgenerator.characters', [
            'numbers' => '0123456789',
            'lowercase' => 'abcdefghijklmnopqrstuvwxyz',
            'uppercase' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'symbols' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
        ]);

        $this->numbers = $config['numbers'];
        $this->lowercase = $config['lowercase'];
        $this->uppercase = $config['uppercase'];
        $this->symbols = $config['symbols'];
    }

    /**
     * تولید رمز عبور تصادفی
     *
     * @param int $length طول رمز عبور
     * @param bool $includeNumbers شامل اعداد
     * @param bool $includeLowercase شامل حروف کوچک
     * @param bool $includeUppercase شامل حروف بزرگ
     * @param bool $includeSymbols شامل سیمبل‌ها
     * @return string رمز عبور تولید شده
     * @throws \Exception
     */
    public function generate(
        int $length,
        bool $includeNumbers = false,
        bool $includeLowercase = false,
        bool $includeUppercase = false,
        bool $includeSymbols = false
    ): string {
        // ساخت مجموعه کاراکترهای مجاز
        $characters = $this->buildCharacterSet(
            $includeNumbers,
            $includeLowercase,
            $includeUppercase,
            $includeSymbols
        );

        if (empty($characters)) {
            throw new \Exception('No character types selected');
        }

        // تولید رمز عبور
        return $this->generateRandomPassword($characters, $length);
    }

    /**
     * ساخت مجموعه کاراکترهای مجاز
     */
    private function buildCharacterSet(
        bool $includeNumbers,
        bool $includeLowercase,
        bool $includeUppercase,
        bool $includeSymbols
    ): string {
        $characters = '';

        if ($includeNumbers) {
            $characters .= $this->numbers;
        }
        if ($includeLowercase) {
            $characters .= $this->lowercase;
        }
        if ($includeUppercase) {
            $characters .= $this->uppercase;
        }
        if ($includeSymbols) {
            $characters .= $this->symbols;
        }

        return $characters;
    }

    /**
     * تولید رمز عبور تصادفی از روی کاراکترهای مجاز
     */
    private function generateRandomPassword(string $characters, int $length): string
    {
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $password;
    }

    /**
     * بررسی قدرت رمز عبور (اختیاری - برای آینده)
     */
    public function checkStrength(string $password): array
    {
        $strength = [
            'length' => strlen($password),
            'has_numbers' => preg_match('/[0-9]/', $password) ? true : false,
            'has_lowercase' => preg_match('/[a-z]/', $password) ? true : false,
            'has_uppercase' => preg_match('/[A-Z]/', $password) ? true : false,
            'has_symbols' => preg_match('/[^a-zA-Z0-9]/', $password) ? true : false,
        ];

        return $strength;
    }
}