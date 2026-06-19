<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class racks extends Model
{
    protected $table = 'racks';

    protected $fillable = [
        'name',
        'code',
        'location_note',
        'capacity',
    ];

    public function buku(): HasMany
    {
        return $this->hasMany(Book::class, 'rack_id');
    }

    public function totalBuku(): int
    {
        return (int) $this->buku()->sum('stock');
    }

    // Backward compatibility (views may call totalBooks())
    public function totalBooks(): int
    {
        return $this->totalBuku();
    }

}
