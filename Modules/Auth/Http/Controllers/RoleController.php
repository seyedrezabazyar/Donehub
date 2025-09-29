<?php
// Modules/Auth/Http/Controllers/RoleController.php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\Role;
use Modules\Auth\Models\User;
use Modules\Auth\Models\Permission;

class RoleController extends Controller
{
    /**
     * نمایش لیست نقش‌ها
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::withCount('users');

        if ($request->has('with_permissions')) {
            $query->with('permissions:id,name,display_name');
        }

        $roles = $query->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * ایجاد نقش جدید
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:roles|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        $role = Role::create($request->only(['name', 'display_name', 'description']));

        if ($request->has('permission_ids')) {
            $role->permissions()->attach($request->permission_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'نقش با موفقیت ایجاد شد',
            'data' => $role->load('permissions:id,name,display_name')
        ], 201);
    }

    /**
     * نمایش جزئیات نقش
     */
    public function show($id): JsonResponse
    {
        $role = Role::with(['permissions', 'users' => function($query) {
            $query->select('users.id', 'users.name', 'users.email')
                ->limit(10);
        }])->withCount('users')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    /**
     * به‌روزرسانی نقش
     */
    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // نقش admin قابل ویرایش نیست
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'نقش مدیر قابل ویرایش نیست'
            ], 403);
        }

        $request->validate([
            'display_name' => 'sometimes|string|max:100',
            'description' => 'nullable|string'
        ]);

        $role->update($request->only(['display_name', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'نقش با موفقیت به‌روزرسانی شد',
            'data' => $role
        ]);
    }

    /**
     * حذف نقش
     */
    public function destroy($id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // نقش‌های سیستمی قابل حذف نیستند
        if (in_array($role->name, ['admin', 'editor', 'user'])) {
            return response()->json([
                'success' => false,
                'message' => 'نقش‌های سیستمی قابل حذف نیستند'
            ], 403);
        }

        // بررسی کاربران دارای این نقش
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'این نقش دارای کاربران فعال است و قابل حذف نیست'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'نقش با موفقیت حذف شد'
        ]);
    }

    /**
     * اختصاص نقش به کاربر
     */
    public function assignRole(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role_id);

        // بررسی عدم تکرار
        if ($user->hasRole($role->name)) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر از قبل این نقش را دارد'
            ], 400);
        }

        $user->assignRole($role->name, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'نقش با موفقیت اختصاص داده شد',
            'data' => $user->load('roles:id,name,display_name')
        ]);
    }

    /**
     * حذف نقش از کاربر
     */
    public function removeRole(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role_id);

        // کاربر باید حداقل یک نقش داشته باشد
        if ($user->roles()->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر باید حداقل یک نقش داشته باشد'
            ], 400);
        }

        $user->removeRole($role->name);

        return response()->json([
            'success' => true,
            'message' => 'نقش با موفقیت حذف شد',
            'data' => $user->load('roles:id,name,display_name')
        ]);
    }

    /**
     * دریافت کاربران یک نقش
     */
    public function getUsers(Request $request, $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        $users = $role->users()
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name
            ],
            'users' => $users
        ]);
    }
}
