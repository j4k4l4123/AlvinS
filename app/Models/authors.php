<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class authors extends Model
{
    protected $table = 'authors';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function buku(): HasMany
    {
        return $this->hasMany(Book::class, 'author_id');
    }
}
