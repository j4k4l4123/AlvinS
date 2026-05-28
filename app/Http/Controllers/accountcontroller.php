<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pinjam;
use App\Services\FineService;
use Illuminate\Contracts\View\View;

class AccountController extends Controller
{
    public function show(FineService $fineService): View
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;
        $anggota = $user?->anggota;

        if (! $anggota && $profile?->id_anggota) {
            $anggota = Anggota::where('id_anggota', $profile->id_anggota)->first();
        }

        $libraryCard = $anggota?->libraryCard;
        $activeBorrowingsCount = $anggota
            ? Pinjam::where('anggota_id', $anggota->id)->where('status', 'dipinjam')->count()
            : 0;
        $totalFines = $anggota ? $fineService->getTotalFines($anggota->id) : 0;

        return view('account.show', compact(
            'user',
            'profile',
            'anggota',
            'libraryCard',
            'activeBorrowingsCount',
            'totalFines'
        ));
    }
}
