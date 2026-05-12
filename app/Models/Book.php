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
        'stock',
    ];

    public function pinjam(): HasMany
    {
        return $this->hasMany(Pinjam::class);
    }

    public function pengembalian(): HasMany
    {
        return $this->hasMany(Pengembalian::class);
    }

    public function activeBorrowingsCount(): int
    {
        return $this->pinjam()->where('status', 'dipinjam')->count();
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock - $this->activeBorrowingsCount());
    }

    public function isAvailable(): bool
    {
        return $this->availableStock() > 0;
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->whereRaw('LOWER(judul) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(pengarang) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('LOWER(kategori) LIKE ?', ['%' . strtolower($keyword) . '%'])
                ->orWhereRaw('CAST(thn_terbit AS CHAR) LIKE ?', ['%' . strtolower($keyword) . '%']);
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
