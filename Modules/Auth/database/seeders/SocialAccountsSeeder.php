<?php

namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// SocialAccountsSeeder.php
class SocialAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = DB::table('users')->pluck('id')->take(3);
        $providers = ['google', 'github', 'facebook'];

        foreach ($userIds as $index => $userId) {
            $provider = $providers[$index];

            DB::table('social_accounts')->insert([
                'user_id' => $userId,
                'provider' => $provider,
                'provider_id' => $provider . '_' . rand(100000, 999999),
                'name' => DB::table('users')->where('id', $userId)->value('name'),
                'email' => DB::table('users')->where('id', $userId)->value('email'),
                'avatar' => 'https://avatars.' . $provider . '.com/user' . rand(1000, 9999),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);
        }
    }
}
