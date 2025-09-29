<?php
// Modules/Auth/Models/Permission.php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'display_name', 'group', 'description'];

    /**
     * نقش‌های دارای این دسترسی
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permissions',
            'permission_id',
            'role_id'
        );
    }
}
