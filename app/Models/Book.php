<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'id_buku',
        'judul',
        'pengarang',
        'penerbit',
        'thn_terbit',
        'kategori',
        'keterangan',
    ];
}