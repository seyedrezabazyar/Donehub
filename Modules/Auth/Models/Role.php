<?php
// Modules/Auth/Models/Role.php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];

    /**
     * کاربران دارای این نقش
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id'
        )->withPivot('assigned_by', 'assigned_at');
    }

    /**
     * دسترسی‌های این نقش
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * بررسی داشتن یک دسترسی خاص
     */
    public function hasPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }

        return $this->permissions()->where('id', $permission->id)->exists();
    }

    /**
     * اختصاص دسترسی به نقش
     */
    public function givePermission($permission)
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)->first()
            : $permission;

        if ($permissionModel && !$this->hasPermission($permissionModel)) {
            $this->permissions()->attach($permissionModel->id);
        }

        return $this;
    }

    /**
     * حذف دسترسی از نقش
     */
    public function revokePermission($permission)
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)->first()
            : $permission;

        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }

        return $this;
    }
}
