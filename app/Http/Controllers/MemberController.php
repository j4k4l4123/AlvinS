<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\MembershipRequest;
use App\Models\Pinjam;
use App\Services\FineService;

class MemberController extends Controller
{
    public function dashboard(FineService $fineService)
    {
        $user = auth()->user();
        $profile = $user?->memberProfile;
        $anggota = $user?->anggota;

        if (! $anggota && $profile?->id_anggota) {
            $anggota = Anggota::where('id_anggota', $profile->id_anggota)->first();
        }

        $activeBorrowings = $anggota
            ? Pinjam::with(['book', 'pengembalian'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->latest()
                ->paginate(5, ['*'], 'active_page')
            : collect();

        $borrowingHistory = $anggota
            ? Pinjam::with(['book', 'pengembalian'])
                ->where('anggota_id', $anggota->id)
                ->latest()
                ->paginate(10, ['*'], 'history_page')
            : collect();

        $libraryCard = $anggota?->libraryCard;
        $pendingCancellation = $user
            ? MembershipRequest::where('user_id', $user->id)
                ->where('type', 'cancellation')
                ->where('status', 'pending')
                ->latest()
                ->first()
            : null;

        $totalFines = $anggota ? $fineService->getTotalFines($anggota->id) : 0;

        return view('member.dashboard', compact(
            'profile',
            'anggota',
            'activeBorrowings',
            'borrowingHistory',
            'libraryCard',
            'pendingCancellation',
            'totalFines'
        ));
    }
}
