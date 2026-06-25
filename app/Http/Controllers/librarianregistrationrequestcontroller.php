<?php

namespace App\Http\Controllers;

use App\Models\LibrarianRegistrationRequest;
use App\Models\Role;
use App\Models\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class LibrarianRegistrationRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (! Schema::hasTable('librarian_registration_requests')) {
            return back()->with('error', 'Fitur permintaan librarian belum aktif. Jalankan migrasi database terlebih dahulu.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        if (! $user || ! $user->isMember()) {
            abort(403);
        }

        if ($user->isLibrarian()) {
            return back()->with('error', 'Akun ini sudah memiliki akses librarian.');
        }

        $existing = LibrarianRegistrationRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existing) {
            return back()->with('error', 'Permintaan librarian kamu masih menunggu persetujuan.');
        }

        LibrarianRegistrationRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'Permintaan menjadi librarian berhasil dikirim dan sedang menunggu persetujuan.');
    }

    public function index(): View
    {
        $requests = Schema::hasTable('librarian_registration_requests')
            ? LibrarianRegistrationRequest::with(['user', 'processedBy'])->latest()->paginate(10)
            : collect();

        return view('librarian-registration-requests.index', compact('requests'));
    }

    public function update(Request $request, LibrarianRegistrationRequest $librarianRegistrationRequest): RedirectResponse
    {
        if (! Schema::hasTable('librarian_registration_requests')) {
            return back()->with('error', 'Fitur permintaan librarian belum aktif. Jalankan migrasi database terlebih dahulu.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($librarianRegistrationRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        DB::transaction(function () use ($validated, $librarianRegistrationRequest) {
            $librarianRegistrationRequest->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            if ($validated['status'] === 'approved') {
                $role = Role::findByName('librarian');
                if ($role) {
                    DB::table('role_user')->updateOrInsert([
                        'user_id' => $librarianRegistrationRequest->user_id,
                        'role_id' => $role->id,
                    ]);
                }
            }

            SystemNotification::create([
                'user_id' => $librarianRegistrationRequest->user_id,
                'type' => 'librarian_request_' . $validated['status'],
                'title' => $validated['status'] === 'approved' ? 'Akses librarian disetujui' : 'Akses librarian ditolak',
                'message' => $validated['status'] === 'approved'
                    ? 'Selamat! Permintaan akses librarian kamu telah disetujui. Silakan login ulang jika menu librarian belum langsung muncul.'
                    : 'Maaf, permintaan akses librarian kamu ditolak.' . (($validated['notes'] ?? null) ? ' Catatan: ' . $validated['notes'] : ''),
                'data' => [
                    'request_id' => $librarianRegistrationRequest->id,
                    'status' => $validated['status'],
                ],
            ]);
        });

        return back()->with('success', 'Permintaan librarian berhasil diproses.');
    }
}
