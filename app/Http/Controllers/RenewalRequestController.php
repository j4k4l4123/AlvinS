<?php

namespace App\Http\Controllers;

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
        $requests = DB::table('renewal_requests')
            ->leftJoin('users', 'renewal_requests.user_id', '=', 'users.id')
            ->leftJoin('anggota', 'renewal_requests.anggota_id', '=', 'anggota.id')
            ->leftJoin('pinjam', 'renewal_requests.pinjam_id', '=', 'pinjam.id')
            ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
            ->leftJoin('users as processed_users', 'renewal_requests.processed_by', '=', 'processed_users.id')
            ->select([
                'renewal_requests.*',
                'users.name as user_name',
                'anggota.nama as anggota_nama',
                'books.judul as book_judul',
                'pinjam.tanggal_kembali as pinjam_tanggal_kembali',
                'pinjam.tanggal_pinjam as pinjam_tanggal_pinjam',
                'processed_users.name as processed_by_name'
            ])
            ->orderByDesc('renewal_requests.created_at')
            ->paginate(10);

        $requests->getCollection()->transform(function ($item) {
            $item->user = (object) ['name' => $item->user_name ?? '-'];
            $item->anggota = (object) ['nama' => $item->anggota_nama ?? '-'];
            $item->borrowing = (object) [
                'book' => (object) ['judul' => $item->book_judul ?? '-'],
                'tanggal_kembali' => $item->pinjam_tanggal_kembali ? \Carbon\Carbon::parse($item->pinjam_tanggal_kembali) : null,
                'tanggal_pinjam' => $item->pinjam_tanggal_pinjam ? \Carbon\Carbon::parse($item->pinjam_tanggal_pinjam) : null,
            ];
            $item->processedBy = $item->processed_by_name ? (object) ['name' => $item->processed_by_name] : null;
            if (isset($item->created_at)) {
                $item->created_at = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : null;
            }
            return $item;
        });

        return view('renewal-requests.index', compact('requests'));
    }

    public function show($id)
    {
        $renewalRequest = DB::table('renewal_requests')->where('id', $id)->first();
        abort_if(!$renewalRequest, 404);

        $renewalRequest->user = DB::table('users')->where('id', $renewalRequest->user_id)->first();
        $renewalRequest->anggota = DB::table('anggota')->where('id', $renewalRequest->anggota_id)->first();
        $renewalRequest->processedBy = $renewalRequest->processed_by ? DB::table('users')->where('id', $renewalRequest->processed_by)->first() : null;
        $renewalRequest->borrowing = DB::table('pinjam')->where('id', $renewalRequest->pinjam_id)->first();
        if ($renewalRequest->borrowing) {
            $renewalRequest->borrowing->book = DB::table('books')->where('id', $renewalRequest->borrowing->book_id)->first();
            if ($renewalRequest->borrowing->tanggal_pinjam) {
                $renewalRequest->borrowing->tanggal_pinjam = \Carbon\Carbon::parse($renewalRequest->borrowing->tanggal_pinjam);
            }
            if ($renewalRequest->borrowing->tanggal_kembali) {
                $renewalRequest->borrowing->tanggal_kembali = \Carbon\Carbon::parse($renewalRequest->borrowing->tanggal_kembali);
            }
        }

        if ($renewalRequest->processed_at) {
            $renewalRequest->processed_at = \Carbon\Carbon::parse($renewalRequest->processed_at);
        }
        if ($renewalRequest->created_at) {
            $renewalRequest->created_at = \Carbon\Carbon::parse($renewalRequest->created_at);
        }

        return view('renewal-requests.show', compact('renewalRequest'));
    }

    public function update(Request $request, $id)
    {
        $renewalRequest = DB::table('renewal_requests')->where('id', $id)->first();
        abort_if(!$renewalRequest, 404);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        $borrowing = DB::table('pinjam')->where('id', $renewalRequest->pinjam_id)->first();

        DB::transaction(function () use ($id, $renewalRequest, $borrowing, $validated) {
            if ($validated['status'] === 'approved' && $borrowing && $borrowing->status === 'dipinjam') {
                $this->borrowingService->renew($borrowing);
            }

            DB::table('renewal_requests')->where('id', $id)->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? $renewalRequest->notes,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);
        });

        if ($renewalRequest->user_id) {
            $book = $borrowing ? DB::table('books')->where('id', $borrowing->book_id)->first() : null;
            NotificationHelper::send(
                $renewalRequest->user_id,
                'renewal_request_' . $validated['status'],
                $validated['status'] === 'approved' ? 'Perpanjangan disetujui' : 'Perpanjangan ditolak',
                $validated['status'] === 'approved'
                    ? 'Permintaan perpanjangan untuk buku "' . ($book?->judul ?? '-') . '" telah disetujui.'
                    : 'Permintaan perpanjangan untuk buku "' . ($book?->judul ?? '-') . '" ditolak.' . (($validated['notes'] ?? null) ? ' Catatan: ' . $validated['notes'] : ''),
                [
                    'renewal_request_id' => $renewalRequest->id,
                    'status' => $validated['status'],
                ]
            );
        }

        return redirect()->route('membership-requests.renewals')->with('success', 'Permintaan perpanjangan berhasil diproses.');
    }
}
