<?php

namespace App\Http\Controllers;

use App\Models\RenewalRequest;
use App\Services\BorrowingService;
use App\Support\NotificationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RenewalRequestController extends Controller
{
    public function __construct(protected BorrowingService $borrowingService)
    {
    }

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

        DB::transaction(function () use ($renewalRequest, $validated) {
            if ($validated['status'] === 'approved' && $renewalRequest->borrowing?->status === 'dipinjam') {
                $this->borrowingService->renew($renewalRequest->borrowing);
            }

            $renewalRequest->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $renewalRequest->notes,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        if ($renewalRequest->user_id) {
            NotificationHelper::send(
                $renewalRequest->user_id,
                'renewal_request_' . $validated['status'],
                $validated['status'] === 'approved' ? 'Perpanjangan disetujui' : 'Perpanjangan ditolak',
                $validated['status'] === 'approved'
                    ? 'Permintaan perpanjangan untuk buku "' . ($renewalRequest->borrowing?->book?->judul ?? '-') . '" telah disetujui.'
                    : 'Permintaan perpanjangan untuk buku "' . ($renewalRequest->borrowing?->book?->judul ?? '-') . '" ditolak.' . (($validated['notes'] ?? null) ? ' Catatan: ' . $validated['notes'] : ''),
                [
                    'renewal_request_id' => $renewalRequest->id,
                    'status' => $validated['status'],
                ]
            );
        }

        return redirect()->route('membership-requests.renewals')->with('success', 'Permintaan perpanjangan berhasil diproses.');
    }
}
