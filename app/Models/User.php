<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'user_type_id',
        'username',
        'fullname',
        'mobile',
        'email',
        'dept_id',
        'role_id',
        'password',
        'status',
        'is_email',
        'is_sms',
        'password_change',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->syncRoleFromRoleId();
        });

        static::updated(function (User $user) {
            if ($user->isDirty('role_id')) {
                $user->syncRoleFromRoleId();
            }
        });
    }

    public function syncRoleFromRoleId(): void
    {
        $roleId = (int) $this->role_id;

        if (! $roleId) {
            return;
        }

        try {
            $role = Role::findById($roleId, $this->guard_name);
            $this->syncRoles([$role->name]);
        } catch (\Throwable $e) {
            // Invalid or missing role_id; do not fail the save operation.
        }
    }
}
