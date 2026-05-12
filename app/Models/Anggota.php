<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'id_anggota',
        'nama',
        'alamat',
        'no_tlp',
        'tanggal_daftar',
        'user_id',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pinjam(): HasMany
    {
        return $this->hasMany(Pinjam::class);
    }

    public function pengembalian(): HasMany
    {
        return $this->hasMany(Pengembalian::class);
    }

    public function activeBorrowings()
    {
        return $this->pinjam()->where('status', 'dipinjam');
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(nama) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(id_anggota) LIKE ?', ['%' . strtolower($keyword) . '%']);
        });
    }
}

