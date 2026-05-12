<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function pinjam(): HasMany
    {
        return $this->hasMany(Pinjam::class);
    }

    public function pengembalian(): HasMany
    {
        return $this->hasMany(Pengembalian::class);
    }

    public function isAvailable(): bool
    {
        return !$this->pinjam()->where('status', 'dipinjam')->exists();
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(judul) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . strtolower($keyword) . '%']);
        });
    }

    public function scopeFilterByCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where('kategori', $category);
        }
        return $query;
    }
}
