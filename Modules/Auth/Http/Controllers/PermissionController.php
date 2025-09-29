<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\Permission;
use Modules\Auth\Models\Role;

class PermissionController extends Controller
{
    /**
     * نمایش لیست همه دسترسی‌ها
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::select('id', 'name', 'display_name', 'group', 'description')
            ->orderBy('group')
            ->orderBy('display_name')
            ->get()
            ->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * ایجاد دسترسی جدید
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions',
            'display_name' => 'required|string|max:100',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string'
        ]);

        $permission = Permission::create($request->only(['name', 'display_name', 'group', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'دسترسی با موفقیت ایجاد شد',
            'data' => $permission
        ], 201);
    }

    /**
     * به‌روزرسانی دسترسی
     */
    public function update(Request $request, $id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'display_name' => 'sometimes|string|max:100',
            'group' => 'nullable|string|max:50',
            'description' => 'nullable|string'
        ]);

        $permission->update($request->only(['display_name', 'group', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'دسترسی با موفقیت به‌روزرسانی شد',
            'data' => $permission
        ]);
    }

    /**
     * حذف دسترسی
     */
    public function destroy($id): JsonResponse
    {
        $permission = Permission::findOrFail($id);

        // بررسی استفاده از دسترسی
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'این دسترسی در حال استفاده است و قابل حذف نیست'
            ], 400);
        }

        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'دسترسی با موفقیت حذف شد'
        ]);
    }

    /**
     * دریافت دسترسی‌های یک نقش
     */
    public function getRolePermissions($roleId): JsonResponse
    {
        $role = Role::with('permissions:id,name,display_name,group')->findOrFail($roleId);

        return response()->json([
            'success' => true,
            'data' => [
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name
                ],
                'permissions' => $role->permissions
            ]
        ]);
    }

    /**
     * به‌روزرسانی دسترسی‌های یک نقش
     */
    public function updateRolePermissions(Request $request, $roleId): JsonResponse
    {
        $request->validate([
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($roleId);

        // نقش admin نباید تغییر کند
        if ($role->name === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی‌های نقش مدیر قابل تغییر نیست'
            ], 403);
        }

        $role->permissions()->sync($request->permission_ids);

        return response()->json([
            'success' => true,
            'message' => 'دسترسی‌های نقش با موفقیت به‌روزرسانی شد',
            'data' => $role->load('permissions:id,name,display_name')
        ]);
    }
}
