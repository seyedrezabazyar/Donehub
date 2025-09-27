<?php
// database/seeders/UsersSeeder.php

namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // بررسی وجود کاربر ادمین - اگر وجود نداشت ایجاد کن
        $adminUser = DB::table('users')->where('email', 'admin@example.com')->first();

        if (!$adminUser) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'مدیر سیستم',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'username_last_changed_at' => now()->subYear(),
                'password' => Hash::make('password'),
                'phone' => '+989121234567',
                'phone_verified_at' => now(),
                'national_id' => null,
                'avatar' => '/storage/avatars/admin.jpg',
                'preferred_method' => 'password',
                'failed_attempts' => 0,
                'last_login_at' => now(),
                'province_id' => 1, // Tehran
                'city_id' => 1, // Tehran city
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $adminId = $adminUser->id;
        }

        // بررسی وجود پروفایل ادمین
        $adminProfile = DB::table('user_profiles')->where('user_id', $adminId)->first();
        if (!$adminProfile) {
            DB::table('user_profiles')->insert([
                'user_id' => $adminId,
                'bio' => 'مدیر سیستم پلتفرم کتاب و مقاله',
                'avatar_path' => '/storage/avatars/admin.jpg',
                'visibility' => 'public',
                'show_achievements' => true,
                'show_statistics' => true,
                'total_points' => 10000,
                'current_level' => 10,
                'reputation_score' => 1000,
                'referral_code' => $this->generateUniqueReferralCode(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // اختصاص نقش ادمین - بررسی وجود نقش و اختصاص قبلی
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            $existingRole = DB::table('user_roles')
                ->where('user_id', $adminId)
                ->where('role_id', $adminRole->id)
                ->first();

            if (!$existingRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $adminId,
                    'role_id' => $adminRole->id,
                    'assigned_by' => null,
                    'assigned_at' => now(),
                ]);
            }
        }

        // ایجاد کاربران نمونه
        $users = [
            ['name' => 'احمد محمدی', 'email' => 'ahmad@example.com'],
            ['name' => 'مریم احمدی', 'email' => 'maryam@example.com'],
            ['name' => 'علی رضایی', 'email' => 'ali@example.com'],
            ['name' => 'زهرا کریمی', 'email' => 'zahra@example.com'],
            ['name' => 'حسین موسوی', 'email' => 'hossein@example.com'],
        ];

        foreach ($users as $index => $user) {
            // بررسی وجود کاربر قبل از ایجاد
            $existingUser = DB::table('users')->where('email', $user['email'])->first();
            if ($existingUser) {
                continue; // اگر کاربر وجود دارد، رد شو
            }

            $username = $this->generateUniqueUsername();

            $userId = DB::table('users')->insertGetId([
                'name' => $user['name'],
                'username' => $username,
                'email' => $user['email'],
                'email_verified_at' => rand(0, 1) ? now() : null,
                'username_last_changed_at' => rand(0, 1) ? now()->subMonths(rand(1, 11)) : null,
                'password' => Hash::make('password'),
                'phone' => $this->generatePhoneNumber(),
                'phone_verified_at' => rand(0, 1) ? now() : null,
                'national_id' => rand(0, 1) ? (string)rand(1000000000, 9999999999) : null,
                'avatar' => $this->generateAvatarPath($username),
                'preferred_method' => rand(0, 1) ? 'password' : 'otp',
                'failed_attempts' => 0,
                'last_login_at' => rand(0, 1) ? now()->subDays(rand(1, 30)) : null,
                'province_id' => rand(1, 8),
                'city_id' => rand(1, 18),
                'is_admin' => false,
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now(),
            ]);

            // ایجاد پروفایل
            $this->createUserProfile($userId, $user['name'], $username);

            // اختصاص نقش‌ها - فقط اگر نقش وجود داشته باشد
            if ($index < 4) {
                $roleNames = ['editor', 'gallery_manager', 'content_moderator', 'support'];
                $roleName = $roleNames[$index % 4];
                $role = DB::table('roles')->where('name', $roleName)->first();

                if ($role) {
                    $this->assignUserRole($userId, $role->id, $adminId);
                }
            }

            // ایجاد device sessions
            $this->createDeviceSession($userId, $username);

            // ایجاد security logs
            $this->createSecurityLogs($userId, $user['email']);

            // ایجاد login streak
            $this->createLoginStreak($userId);
        }

        $this->command->info('✅ Users seeded successfully!');
        $this->command->info('🔐 Admin: admin@example.com / password');
        $this->command->info('👤 Users: ahmad@example.com / password');
    }

    private function generateUniqueUsername(): string
    {
        do {
            $username = 'user_' . strtolower(Str::random(6));
        } while (DB::table('users')->where('username', $username)->exists());

        return $username;
    }

    private function generatePhoneNumber(): string
    {
        $prefixes = ['912', '913', '914', '915', '916', '917', '918', '919'];
        return '+98' . $prefixes[array_rand($prefixes)] . rand(1000000, 9999999);
    }

    private function generateAvatarPath(string $username): ?string
    {
        return rand(0, 10) < 8 ? '/storage/avatars/' . $username . '.jpg' : null;
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (DB::table('user_profiles')->where('referral_code', $code)->exists());

        return $code;
    }

    private function createUserProfile($userId, $name, $username): void
    {
        DB::table('user_profiles')->insert([
            'user_id' => $userId,
            'bio' => 'کاربر فعال در حوزه کتاب و مقالات - ' . $name,
            'avatar_path' => $this->generateAvatarPath($username),
            'website' => rand(0, 1) ? 'https://example.com/' . $username : null,
            'visibility' => ['public', 'members_only', 'private'][rand(0, 2)],
            'show_achievements' => (bool)rand(0, 1),
            'show_statistics' => (bool)rand(0, 1),
            'total_points' => rand(100, 5000),
            'current_level' => rand(1, 10),
            'reputation_score' => rand(0, 1000),
            'referral_code' => $this->generateUniqueReferralCode(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function assignUserRole($userId, $roleId, $adminId): void
    {
        DB::table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_by' => $adminId,
            'assigned_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    private function createDeviceSession($userId, $username): void
    {
        if (rand(0, 1)) {
            DB::table('device_sessions')->insert([
                'user_id' => $userId,
                'device_id' => hash('sha256', 'device_' . $userId . '_' . rand(1000, 9999)),
                'device_name' => ['iPhone 15', 'Samsung S24', 'MacBook Pro', 'Windows PC'][rand(0, 3)],
                'device_type' => ['mobile', 'tablet', 'desktop'][rand(0, 2)],
                'browser' => ['Chrome', 'Firefox', 'Safari', 'Edge'][rand(0, 3)],
                'browser_version' => rand(100, 120) . '.0',
                'platform' => ['Windows', 'macOS', 'iOS', 'Android'][rand(0, 3)],
                'platform_version' => rand(10, 15) . '.0',
                'ip_address' => $this->generateRandomIP(),
                'location' => json_encode([
                    'country' => 'Iran',
                    'city' => ['Tehran', 'Isfahan', 'Shiraz', 'Mashhad'][rand(0, 3)],
                ]),
                'is_trusted' => (bool)rand(0, 1),
                'last_activity' => now()->subDays(rand(0, 7)),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
        }
    }

    private function createSecurityLogs($userId, $email): void
    {
        $events = ['login', 'logout', 'failed_login', 'password_changed'];

        for ($i = 0; $i < rand(3, 8); $i++) {
            $event = $events[array_rand($events)];

            DB::table('security_logs')->insert([
                'event_type' => $event,
                'user_id' => $userId,
                'identifier' => $email,
                'ip_address' => $this->generateRandomIP(),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0',
                'risk_level' => $event === 'failed_login' ? 'medium' : 'low',
                'metadata' => json_encode(['browser' => 'Chrome', 'os' => 'Windows']),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }

    private function createLoginStreak($userId): void
    {
        if (rand(0, 1)) {
            DB::table('daily_login_streaks')->insert([
                'user_id' => $userId,
                'current_streak' => rand(0, 30),
                'longest_streak' => rand(5, 60),
                'last_login_date' => now()->toDateString(),
                'total_login_days' => rand(10, 200),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function generateRandomIP(): string
    {
        return rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255);
    }
}
