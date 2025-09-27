<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Cache;

class PhoneService
{
    private const IRANIAN_PREFIXES = [
        '901', '902', '903', '905', '910', '911', '912', '913', '914',
        '915', '916', '917', '918', '919', '920', '921', '922', '930',
        '933', '934', '935', '936', '937', '938', '939', '990', '991',
        '992', '993', '994', '995', '996', '997', '998', '999'
    ];

    public function normalize(string $phone): string
    {
        $clean = preg_replace('/\D/', '', trim($phone));

        if (strlen($clean) < 7) {
            throw new \InvalidArgumentException('شماره تلفن خیلی کوتاه است');
        }

        $cacheKey = "phone_norm:" . md5($clean);

        try {
            return Cache::remember($cacheKey, 3600, function () use ($clean) {
                return $this->performNormalization($clean);
            });
        } catch (\Exception $e) {
            // If caching fails, still try to normalize
            return $this->performNormalization($clean);
        }
    }

    private function performNormalization(string $clean): string
    {
        // Iranian formats
        if ($this->isIranianFormat($clean)) {
            return $this->normalizeIranian($clean);
        }

        // International formats
        if (str_starts_with($clean, '00')) {
            return '+' . substr($clean, 2);
        }

        if (preg_match('/^(1|44|49|33|39|34|7|86|91|81|82|61|55|52|54|56|51|57|58|595|598)/', $clean)) {
            return '+' . $clean;
        }

        return '+' . $clean;
    }

    private function isIranianFormat(string $clean): bool
    {
        return preg_match('/^(98|0)?9\d{9}$/', $clean) ||
            preg_match('/^00989\d{9}$/', $clean);
    }

    private function normalizeIranian(string $clean): string
    {
        if (preg_match('/^00989(\d{9})$/', $clean, $matches)) {
            return '+989' . $matches[1];
        }

        if (preg_match('/^989(\d{9})$/', $clean, $matches)) {
            return '+989' . $matches[1];
        }

        if (preg_match('/^09(\d{9})$/', $clean, $matches)) {
            return '+989' . $matches[1];
        }

        if (preg_match('/^9(\d{9})$/', $clean, $matches)) {
            return '+989' . $matches[1];
        }

        return '+98' . ltrim($clean, '0+98');
    }

    public function isIranian(string $phone): bool
    {
        return str_starts_with($this->normalize($phone), '+98');
    }

    public function findUserByPhone(string $phone): ?User
    {
        try {
            $normalized = $this->normalize($phone);
            $user = User::where('phone', $normalized)->first();

            if (!$user && str_starts_with($normalized, '+98')) {
                // Try Iranian variations
                $variations = [
                    '0' . substr($normalized, 3),
                    substr($normalized, 3),
                    '98' . substr($normalized, 3)
                ];

                foreach ($variations as $variation) {
                    $user = User::where('phone', $variation)->first();
                    if ($user) {
                        $user->update(['phone' => $normalized]);
                        break;
                    }
                }
            }

            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isValid(string $phone): bool
    {
        try {
            $clean = preg_replace('/\D/', '', trim($phone));

            if (strlen($clean) < 7 || strlen($clean) > 15) {
                return false;
            }

            if ($this->isIranianFormat($clean)) {
                return $this->validateIranian($clean);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function validateIranian(string $clean): bool
    {
        $number = preg_replace('/^(98|0)*/', '', $clean);

        if (strlen($number) !== 10 || !str_starts_with($number, '9')) {
            return false;
        }

        $prefix = substr($number, 0, 3);
        return in_array($prefix, self::IRANIAN_PREFIXES);
    }

    public function format(string $phone, string $format = 'international'): string
    {
        try {
            $normalized = $this->normalize($phone);

            if ($this->isIranian($normalized)) {
                $number = substr($normalized, 3);

                return match($format) {
                    'national' => '0' . substr($number, 0, 3) . ' ' .
                        substr($number, 3, 3) . ' ' . substr($number, 6),
                    'compact' => '0' . $number,
                    default => '+98 ' . substr($number, 0, 3) . ' ' .
                        substr($number, 3, 3) . ' ' . substr($number, 6)
                };
            }

            return $normalized;
        } catch (\Exception $e) {
            return $phone;
        }
    }
}
