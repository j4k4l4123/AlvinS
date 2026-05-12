<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function memberProfile()
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function libraryCards()
    {
        return $this->hasMany(LibraryCard::class);
    }

    public function hasRole(Role|string $role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }

        return $this->roles()->where('id', $role->id)->exists();
    }

    public function hasAnyRole(array|string $roles): bool
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public function isLibrarian(): bool
    {
        return $this->hasRole('librarian');
    }

    public function isMember(): bool
    {
        return $this->hasRole('member');
    }
}

