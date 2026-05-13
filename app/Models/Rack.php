<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location_note',
        'capacity',
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function totalBooks(): int
    {
        return (int) $this->books()->sum('stock');
    }
}
