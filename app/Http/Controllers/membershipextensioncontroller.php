<?php

namespace App\Http\Controllers;

use App\Support\NotificationHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MembershipExtensionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();
        $anggota = $user?->anggota;
        
        $libraryCard = $anggota
            ? DB::table('library_cards')->where('anggota_id', $anggota->id)->first()
            : null;

        abort_unless($user && $anggota && $libraryCard, 403);

        $existingPending = DB::table('membership_requests')
            ->where('user_id', $user->id)
            ->where('anggota_id', $anggota->id)
            ->where('type', 'renewal')
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->exists();

        if ($existingPending) {
            return back()->with('error', 'Permintaan perpanjangan membership masih menunggu persetujuan librarian.');
        }

        DB::table('membership_requests')->insert([
            'user_id' => $user->id,
            'anggota_id' => $anggota->id,
            'type' => 'renewal',
            'status' => 'pending',
            'reason' => $validated['reason'] ?? 'Permintaan perpanjangan membership.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationHelper::send(
            $user->id,
            'membership_renewal_requested',
            'Permintaan perpanjangan membership dikirim',
            'Permintaan perpanjangan membership kamu berhasil dikirim dan sedang menunggu persetujuan librarian.',
            []
        );

        return back()->with('success', 'Permintaan perpanjangan membership berhasil dikirim.');
    }
}
