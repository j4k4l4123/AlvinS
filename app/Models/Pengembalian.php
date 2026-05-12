<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengembalian extends Model
{
    use HasFactory;

    protected $table = 'pengembalian';

    protected $fillable = [
        'pinjam_id',
        'anggota_id',
        'book_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'denda',
    ];

    protected $casts = [
        'tanggal_pinjam' => 'date',
        'tanggal_kembali' => 'date',
        'tanggal_dikembalikan' => 'date',
        'denda' => 'integer',
    ];

    public function pinjam(): BelongsTo
    {
        return $this->belongsTo(Pinjam::class);
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeForAnggota($query, int $anggotaId)
    {
        return $query->where('anggota_id', $anggotaId);
    }

    public function scopeWithDenda($query)
    {
        return $query->where('denda', '>', 0);
    }
}

