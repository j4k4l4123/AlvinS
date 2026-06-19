<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $table = 'fines';

    protected $fillable = [
        'pinjam_id',
        'pengembalian_id',
        'anggota_id',
        'amount',
        'type',
        'status',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjam::class, 'pinjam_id');
    }

    public function rekaman_pengembalian(): BelongsTo
    {
        return $this->belongsTo(Pengembalian::class, 'pengembalian_id');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class);
    }
}
