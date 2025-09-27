<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // It's recommended to disable foreign key checks during seeding.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables to start fresh
        DB::table('role_permissions')->truncate();
        DB::table('user_roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();

        // ایجاد نقش‌های پیش‌فرض
        $roles = [
            ['name' => 'admin', 'display_name' => 'مدیر سیستم', 'description' => 'دسترسی کامل به سیستم'],
            ['name' => 'editor', 'display_name' => 'ویرایشگر', 'description' => 'مدیریت محتوا'],
            ['name' => 'moderator', 'display_name' => 'ناظر', 'description' => 'مدیریت کامنت‌ها و محتوای کاربران'],
            ['name' => 'writer', 'display_name' => 'نویسنده', 'description' => 'ایجاد و ویرایش مطالب خود'],
            ['name' => 'user', 'display_name' => 'کاربر عادی', 'description' => 'کاربر معمولی سایت']
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // ایجاد دسترسی‌های پیش‌فرض
        $permissions = [
            ['name' => 'users.view', 'display_name' => 'مشاهده کاربران', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'ایجاد کاربر', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'ویرایش کاربران', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'حذف کاربران', 'group' => 'users'],
            ['name' => 'users.manage_roles', 'display_name' => 'مدیریت نقش کاربران', 'group' => 'users'],
            ['name' => 'roles.view', 'display_name' => 'مشاهده نقش‌ها', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'ایجاد نقش', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'ویرایش نقش‌ها', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'حذف نقش‌ها', 'group' => 'roles'],
            ['name' => 'posts.view', 'display_name' => 'مشاهده مطالب', 'group' => 'content'],
            ['name' => 'posts.create', 'display_name' => 'ایجاد مطلب', 'group' => 'content'],
            ['name' => 'posts.edit', 'display_name' => 'ویرایش مطالب', 'group' => 'content'],
            ['name' => 'posts.delete', 'display_name' => 'حذف مطالب', 'group' => 'content'],
            ['name' => 'posts.publish', 'display_name' => 'انتشار مطالب', 'group' => 'content'],
            ['name' => 'comments.view', 'display_name' => 'مشاهده نظرات', 'group' => 'comments'],
            ['name' => 'comments.moderate', 'display_name' => 'مدیریت نظرات', 'group' => 'comments'],
            ['name' => 'comments.delete', 'display_name' => 'حذف نظرات', 'group' => 'comments'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        // اتصال دسترسی‌ها به نقش‌ها
        $rolePermissions = [
            'admin' => ['*'],
            'editor' => ['users.view', 'posts.view', 'posts.create', 'posts.edit', 'posts.delete', 'posts.publish', 'comments.view', 'comments.moderate', 'comments.delete'],
            'moderator' => ['posts.view', 'comments.view', 'comments.moderate', 'comments.delete'],
            'writer' => ['posts.view', 'posts.create', 'posts.edit', 'comments.view'],
            'user' => []
        ];

        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = DB::table('roles')->where('name', $roleName)->first();
            if (!$role) continue;

            $permissionIds = [];
            if ($permissionNames === ['*']) {
                $permissionIds = DB::table('permissions')->pluck('id');
            } else {
                $permissionIds = DB::table('permissions')->whereIn('name', $permissionNames)->pluck('id');
            }

            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // ایجاد یک کاربر ادمین پیش‌فرض
        if (DB::table('users')->where('email', 'admin@example.com')->doesntExist()) {
            $adminUserId = DB::table('users')->insertGetId([
                'name' => 'مدیر سیستم',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $adminRole = DB::table('roles')->where('name', 'admin')->first();
            if ($adminRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $adminUserId,
                    'role_id' => $adminRole->id,
                    'assigned_at' => now()
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
