<?php

namespace App\Http\Controllers;

use App\Models\LibrarianRegistrationRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibrarianRegistrationRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
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
        $requests = LibrarianRegistrationRequest::with(['user', 'processedBy'])
            ->latest()
            ->paginate(10);

        return view('librarian-registration-requests.index', compact('requests'));
    }

    public function update(Request $request, LibrarianRegistrationRequest $librarianRegistrationRequest): RedirectResponse
    {
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
                $role = Role::where('name', 'librarian')->firstOrFail();
                $librarianRegistrationRequest->user->roles()->syncWithoutDetaching([$role->id]);
            }
        });

        return back()->with('success', 'Permintaan librarian berhasil diproses.');
    }
}
