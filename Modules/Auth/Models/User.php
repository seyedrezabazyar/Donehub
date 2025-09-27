<?php
// Modules/Auth/Models/User.php

namespace Modules\Auth\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Database\Factories\UserFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Modules/Auth/Models/User.php
    protected $fillable = [
        'name', 'username', 'email', 'email_verified_at', 'phone', 'phone_verified_at',
        'password', 'preferred_method', 'national_id', 'avatar',
        'failed_attempts', 'locked_until', 'last_login_at', 'is_admin',
        'province_id', 'city_id', 'address', 'username_last_changed_at'
    ];

    protected $hidden = ['password', 'remember_token', 'national_id'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'username_last_changed_at' => 'datetime',
        'failed_attempts' => 'integer',
        'is_admin' => 'boolean'
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->username)) {
                $user->username = static::generateUniqueUsername();
            }
            if (!empty($user->email)) {
                $user->email = strtolower($user->email);
            }
            if (empty($user->email) && empty($user->phone)) {
                throw new \InvalidArgumentException('Email or phone required');
            }
        });
    }

    // متودهای مربوط به یوزرنیم
    public function canChangeUsername(): bool
    {
        if (!$this->username_last_changed_at) {
            return true; // اولین بار که یوزرنیم تنظیم می‌شود
        }

        // Add 1 year to the last changed date
        $nextChangeDate = $this->username_last_changed_at->addYear();

        // Check if the current date is past the next changeable date
        return now()->gte($nextChangeDate);
    }

    public function getDaysUntilUsernameChange(): int
    {
        if (!$this->username_last_changed_at) {
            return 0;
        }

        $nextChangeDate = $this->username_last_changed_at->addYear();
        $now = now();

        if ($now->gte($nextChangeDate)) {
            return 0;
        }

        // Returns the number of full days remaining.
        return $now->diffInDays($nextChangeDate);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public static function generateUniqueUsername(): string
    {
        do {
            $username = substr(str_replace('-', '', Str::uuid()), 0, 8);
        } while (static::where('username', $username)->exists());

        return $username;
    }

    public function setNationalIdAttribute($value): void
    {
        $this->attributes['national_id'] = $value;
    }

    public function getNationalIdAttribute($value): ?string
    {
        return $value;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http') ?
                $this->avatar :
                asset('storage/avatars/' . $this->avatar);
        }

        $initials = $this->getInitials();
        return "https://ui-avatars.com/api/?name={$initials}&size=128&background=0D8ABC&color=fff&rounded=true";
    }

    public function getInitials(): string
    {
        $name = $this->name ?? 'کاربر';
        $parts = explode(' ', $name);

        return count($parts) >= 2 ?
            substr($parts[0], 0, 1) . substr($parts[1], 0, 1) :
            substr($name, 0, 2);
    }

    public function routeNotificationForSms(): ?string
    {
        return $this->phone;
    }

    public function isLocked(): bool
    {
        return $this->locked_until && now()->lt($this->locked_until);
    }

    public function lockAccount(int $minutes = 15): void
    {
        $this->update(['locked_until' => now()->addMinutes($minutes)]);
    }

    public function recordFailedAttempt(): void
    {
        $this->increment('failed_attempts');

        if ($this->failed_attempts >= 5) {
            $this->lockAccount();
        }
    }

    public function clearFailedAttempts(): void
    {
        $this->update([
            'failed_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now()
        ]);
    }

    public function hasVerifiedContact(): bool
    {
        return $this->email_verified_at || $this->phone_verified_at;
    }

    public function getPrimaryContact(): ?string
    {
        if ($this->email_verified_at && $this->email) return $this->email;
        if ($this->phone_verified_at && $this->phone) return $this->phone;
        return $this->email ?: $this->phone;
    }

    public function canLoginWithPassword(): bool
    {
        return !empty($this->password) && !$this->isLocked();
    }

    public function prefersOtpLogin(): bool
    {
        return $this->preferred_method === 'otp' || empty($this->password);
    }

    public function markEmailAsVerified(): void
    {
        if (!$this->email_verified_at) {
            $this->update(['email_verified_at' => now()]);
        }
    }

    public function markPhoneAsVerified(): void
    {
        if (!$this->phone_verified_at) {
            $this->update(['phone_verified_at' => now()]);
        }
    }

    public function scopeByIdentifier($query, string $identifier)
    {
        return $query->where('email', $identifier)->orWhere('phone', $identifier);
    }

    public function scopeVerified($query)
    {
        return $query->where(function ($q) {
            $q->whereNotNull('email_verified_at')->orWhereNotNull('phone_verified_at');
        });
    }

    // ==== روابط جدید برای نقش‌ها ====

    public function roles()
    {
        return $this->belongsToMany(
            \Modules\Auth\Models\Role::class,
            'user_roles',
            'user_id',
            'role_id'
        )->withPivot('assigned_by', 'assigned_at');
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }

        if (is_array($role)) {
            return $this->roles()->whereIn('name', $role)->exists();
        }

        return false;
    }

    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    public function hasAllRoles($roles): bool
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    public function assignRole($role, $assignedBy = null)
    {
        $roleModel = is_string($role)
            ? \Modules\Auth\Models\Role::where('name', $role)->first()
            : $role;

        if (!$roleModel) {
            throw new \Exception("Role not found");
        }

        if (!$this->hasRole($roleModel->name)) {
            $this->roles()->attach($roleModel->id, [
                'assigned_by' => $assignedBy,
                'assigned_at' => now()
            ]);
        }

        return $this;
    }

    public function removeRole($role)
    {
        $roleModel = is_string($role)
            ? \Modules\Auth\Models\Role::where('name', $role)->first()
            : $role;

        if ($roleModel) {
            $this->roles()->detach($roleModel->id);
        }

        return $this;
    }

    public function isAdmin(): bool
    {
        try {
            return $this->is_admin || $this->hasRole('admin');
        } catch (\Exception $e) {
            return (bool) $this->is_admin;
        }
    }

    public function hasPermission($permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyPermission($permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions($permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function getAllPermissions()
    {
        try {
            if (!\Schema::hasTable('roles') || !\Schema::hasTable('permissions')) {
                return collect([]);
            }

            $permissions = collect();

            foreach ($this->roles as $role) {
                if ($role->relationLoaded('permissions')) {
                    $permissions = $permissions->merge($role->permissions);
                }
            }

            return $permissions->unique('id');
        } catch (\Exception $e) {
            \Log::warning('Error getting permissions: ' . $e->getMessage());
            return collect([]);
        }
    }
}
