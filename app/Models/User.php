<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

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
        ];
    }

    public function hasRole(Role|string $role): bool
    {
        $userId = $this->id;
        if ($userId === null) {
            return false;
        }

        if (is_string($role)) {
            $roleRow = Role::findByName($role);
            if ($roleRow === null) {
                return false;
            }

            return Role::userIdsForRole($roleRow)->contains((string) $userId) || Role::userIdsForRole($roleRow)->contains((int) $userId);
        }

        return Role::userIdsForRole($role)->contains((string) $userId) || Role::userIdsForRole($role)->contains((int) $userId);
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
        return $this->hasRole(Role::LIBRARIAN) || $this->hasRole(Role::ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isMember(): bool
    {
        return $this->hasRole(Role::MEMBER);
    }

    public function getMemberProfileAttribute()
    {
        $row = \Illuminate\Support\Facades\DB::table('member_profiles')
            ->where('user_id', $this->id)
            ->first();

        if ($row) {
            if (isset($row->tanggal_daftar) && is_string($row->tanggal_daftar)) {
                $row->tanggal_daftar = \Carbon\Carbon::parse($row->tanggal_daftar);
            }
            if (isset($row->created_at) && is_string($row->created_at)) {
                $row->created_at = \Carbon\Carbon::parse($row->created_at);
            }
            if (isset($row->updated_at) && is_string($row->updated_at)) {
                $row->updated_at = \Carbon\Carbon::parse($row->updated_at);
            }
        }

        return $row;
    }

    public function getAnggotaAttribute()
    {
        $row = \Illuminate\Support\Facades\DB::table('anggota')
            ->where('user_id', $this->id)
            ->first();

        if ($row) {
            if (isset($row->created_at)) {
                $row->created_at = \Carbon\Carbon::parse($row->created_at);
            }
            if (isset($row->updated_at)) {
                $row->updated_at = \Carbon\Carbon::parse($row->updated_at);
            }
            if (isset($row->tanggal_daftar)) {
                $row->tanggal_daftar = \Carbon\Carbon::parse($row->tanggal_daftar);
            }
        }

        return $row;
    }

    public function roles()
    {
        return \Illuminate\Support\Facades\DB::table('roles')
            ->join('role_user', 'roles.id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $this->id)
            ->select('roles.*');
    }

    public function getRolesAttribute()
    {
        return $this->roles()->get();
    }
}

