<?php

namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// DeviceSessionsSeeder.php
class DeviceSessionsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->take(5);

        $devices = [
            ['name' => 'iPhone 15', 'type' => 'mobile', 'browser' => 'Safari', 'platform' => 'iOS'],
            ['name' => 'MacBook Pro', 'type' => 'desktop', 'browser' => 'Chrome', 'platform' => 'macOS'],
            ['name' => 'Samsung S24', 'type' => 'mobile', 'browser' => 'Chrome', 'platform' => 'Android'],
        ];

        foreach ($userIds as $userId) {
            $device = $devices[array_rand($devices)];

            DB::table('device_sessions')->insert([
                'user_id' => $userId,
                'device_id' => hash('sha256', 'device_' . $userId . '_' . rand(1000, 9999)),
                'device_name' => $device['name'],
                'device_type' => $device['type'],
                'browser' => $device['browser'],
                'platform' => $device['platform'],
                'ip_address' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                'is_trusted' => rand(0, 1),
                'last_activity' => now()->subDays(rand(0, 5)),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
