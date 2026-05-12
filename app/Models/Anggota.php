<?php

namespace App\Models;

use App\Models\LibraryCard\LibraryCard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function libraryCard(): HasOne
    {
        return $this->hasOne(LibraryCard::class, 'anggota_id');
    }

    public function membershipRequests(): HasMany
    {
        return $this->hasMany(MembershipRequest::class);
    }

    public function activeBorrowings(): HasMany
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
