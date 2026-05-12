<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberProfile extends Model
{
    protected $fillable = [
        'user_id',
        'id_anggota',
        'nama',
        'alamat',
        'no_tlp',
        'tanggal_daftar',
        'membership_status',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'membership_status' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function borrowings()
    {
        return $this->hasMany(Pinjam::class, 'anggota_id', 'id_anggota');
    }

    public function returns()
    {
        return $this->hasMany(Pengembalian::class, 'anggota_id', 'id_anggota');
    }
}

