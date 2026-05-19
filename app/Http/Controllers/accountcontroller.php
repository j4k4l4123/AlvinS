<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\LibrarianRegistrationRequest;
use App\Models\Pinjam;
use App\Services\FineService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;

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
        $pendingLibrarianRequest = $user && Schema::hasTable('librarian_registration_requests')
            ? LibrarianRegistrationRequest::where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()
                ->first()
            : null;
        $librarianRequestFeatureReady = Schema::hasTable('librarian_registration_requests');

        return view('account.show', compact(
            'user',
            'profile',
            'anggota',
            'libraryCard',
            'activeBorrowingsCount',
            'totalFines',
            'pendingLibrarianRequest',
            'librarianRequestFeatureReady'
        ));
    }
}
