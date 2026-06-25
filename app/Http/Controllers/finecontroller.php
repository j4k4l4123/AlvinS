<?php

namespace App\Http\Controllers;

use App\Services\FineService;
use Illuminate\Support\Facades\DB;

class FineController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $anggota = $user?->anggota;

        $query = DB::table('fines')
            ->leftJoin('pinjam', 'fines.pinjam_id', '=', 'pinjam.id')
            ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
            ->leftJoin('pengembalian', 'pengembalian.pinjam_id', '=', 'pinjam.id')
            ->select([
                'fines.*',
                'books.judul as book_judul',
                'pinjam.tanggal_pinjam as pinjam_tanggal_pinjam',
                'pinjam.tanggal_kembali as pinjam_tanggal_kembali',
                'pengembalian.tanggal_dikembalikan as return_tanggal_dikembalikan'
            ]);

        if ($anggota) {
            $query->where('fines.anggota_id', $anggota->id);
        }

        $fines = $query->orderByDesc('fines.created_at')->paginate(10);

        $fines->getCollection()->transform(function ($item) {
            $item->borrowing = (object) [
                'book' => (object) ['judul' => $item->book_judul ?? '-'],
                'tanggal_pinjam' => $item->pinjam_tanggal_pinjam ? \Carbon\Carbon::parse($item->pinjam_tanggal_pinjam) : null,
                'tanggal_kembali' => $item->pinjam_tanggal_kembali ? \Carbon\Carbon::parse($item->pinjam_tanggal_kembali) : null,
            ];
            $item->returnRecord = $item->return_tanggal_dikembalikan ? (object) [
                'tanggal_dikembalikan' => \Carbon\Carbon::parse($item->return_tanggal_dikembalikan)
            ] : null;
            if (isset($item->created_at)) {
                $item->created_at = \Carbon\Carbon::parse($item->created_at);
            }
            if (isset($item->paid_at)) {
                $item->paid_at = \Carbon\Carbon::parse($item->paid_at);
            }
            return $item;
        });

        return view('member.fines', compact('fines'));
    }

    public function pay($fineId, FineService $fineService)
    {
        $fine = DB::table('fines')->where('id', $fineId)->first();
        abort_if(! $fine, 404);

        $user = auth()->user();
        $anggota = DB::table('anggota')->where('id', $fine->anggota_id)->first();
        abort_if(! $anggota || $anggota->user_id !== $user?->id, 403);

        $pinjam = DB::table('pinjam')->where('id', $fine->pinjam_id)->first();
        abort_if(! $pinjam, 404);

        $fineService->markFineAsPaid($pinjam);

        return back()->with('success', 'Denda berhasil ditandai lunas.');
    }
}
