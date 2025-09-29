<?php

// UserProfilesSeeder.php
namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->whereNotExists(function($query) {
            $query->select(DB::raw(1))
                ->from('user_profiles')
                ->whereColumn('user_profiles.user_id', 'users.id');
        })->get();

        foreach ($users as $user) {
            DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'bio' => 'کاربر فعال پلتفرم - ' . $user->name,
                'website' => rand(0, 1) ? 'https://example.com/' . Str::slug($user->name) : null,
                'visibility' => ['public', 'members_only'][rand(0, 1)],
                'total_points' => rand(50, 1000),
                'current_level' => rand(1, 5),
                'reputation_score' => rand(0, 200),
                'referral_code' => strtoupper(Str::random(8)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
