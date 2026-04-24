<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    public function pinjam()
    {
        return $this->belongsTo(Pinjam::class);
    }

    public function anggota()
    {
        return $this->belongsTo(Anggota::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
