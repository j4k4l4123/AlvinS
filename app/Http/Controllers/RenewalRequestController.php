<?php

namespace App\Http\Controllers;

use App\Models\RenewalRequest;
use App\Services\BorrowingService;
use Illuminate\Http\Request;

class RenewalRequestController extends Controller
{
    public function index()
    {
        $requests = RenewalRequest::with(['user', 'anggota', 'borrowing.book', 'processedBy'])
            ->latest()
            ->paginate(10);

        return view('renewal-requests.index', compact('requests'));
    }

    public function show(RenewalRequest $renewalRequest)
    {
        $renewalRequest->load(['user', 'anggota', 'borrowing.book', 'processedBy']);

        return view('renewal-requests.show', compact('renewalRequest'));
    }

    public function update(Request $request, RenewalRequest $renewalRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $renewalRequest->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $renewalRequest->notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);

        if ($validated['status'] === 'approved' && $renewalRequest->borrowing?->status === 'dipinjam') {
            $renewalRequest->borrowing->update([
                'tanggal_kembali' => $renewalRequest->borrowing->tanggal_kembali->copy()->addDays(BorrowingService::DEFAULT_BORROW_DAYS),
            ]);
        }

        return redirect()->route('renewal-requests.index')->with('success', 'Permintaan perpanjangan berhasil diproses.');
    }
}
