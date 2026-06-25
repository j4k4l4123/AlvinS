<?php

namespace App\Http\Controllers;

use App\Services\FineService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function show(FineService $fineService): View
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;
        $anggota = $user?->anggota;

        if (! $anggota && $profile?->id_anggota) {
            $anggota = DB::table('anggota')->where('id_anggota', $profile->id_anggota)->first();
        }

        $libraryCard = $anggota
            ? DB::table('library_cards')->where('anggota_id', $anggota->id)->first()
            : null;
            
        $activeBorrowingsCount = $anggota
            ? DB::table('pinjam')->where('anggota_id', $anggota->id)->where('status', 'dipinjam')->count()
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
