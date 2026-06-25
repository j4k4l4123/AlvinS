<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengembalianRequest;
use App\Models\Anggota;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use App\Services\FineService;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    protected FineService $fineService;
    protected InventoryService $inventoryService;

    public function __construct(FineService $fineService, InventoryService $inventoryService)
    {
        $this->fineService = $fineService;
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $search = $request->filled('search') ? strtolower((string) $request->search) : null;

        $baseQuery = DB::table('pengembalian')
            ->leftJoin('anggota', 'pengembalian.anggota_id', '=', 'anggota.id')
            ->leftJoin('pinjam', 'pengembalian.pinjam_id', '=', 'pinjam.id')
            ->leftJoin('books', 'pinjam.book_id', '=', 'books.id')
            ->select([
                'pengembalian.*',
                'books.judul as book_judul',
                'books.id_buku as book_id_buku',
                'anggota.nama as anggota_nama',
                'anggota.id_anggota as anggota_id_anggota',
            ])
            ->orderByDesc('pengembalian.id');

        if ($search !== null && $search !== '') {
            $baseQuery->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(books.judul) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(books.id_buku) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(anggota.nama) LIKE ?', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(anggota.id_anggota) LIKE ?', ['%' . $search . '%']);
            });
        }

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $total = (clone $baseQuery)->count();
        $rows = $baseQuery->forPage($page, $perPage)->get();

        foreach ($rows as $row) {
            $row->book = Pengembalian::bookFor($row);
            $row->anggota = Pengembalian::anggotaFor($row);
            $row->pinjam = Pengembalian::pinjamFor($row);
            $row->fine = Pengembalian::fineFor($row);

            $row->denda = $row->fine?->denda ?? ($row->denda ?? 0);

            if (!empty($row->tanggal_pinjam)) {
                $row->tanggal_pinjam = \Carbon\Carbon::parse($row->tanggal_pinjam);
            }
            if (!empty($row->tanggal_kembali)) {
                $row->tanggal_kembali = \Carbon\Carbon::parse($row->tanggal_kembali);
            }
            if (!empty($row->tanggal_dikembalikan)) {
                $row->tanggal_dikembalikan = \Carbon\Carbon::parse($row->tanggal_dikembalikan);
            }
        }

        $pengembalian = new LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $pinjamAktif = DB::table('pinjam')
            ->where('status', 'dipinjam')
            ->get();

        return view('pengembalian.index', compact('pengembalian', 'pinjamAktif'));
    }

    public function create()
    {
        $pinjamList = DB::table('pinjam')
            ->where('status', 'dipinjam')
            ->get();

        foreach ($pinjamList as $p) {
            $p->anggota = \App\Models\Anggota::find($p->anggota_id);
            $p->book = \App\Models\Book::find($p->book_id);
            if ($p->tanggal_kembali) {
                $p->tanggal_kembali = \Carbon\Carbon::parse($p->tanggal_kembali);
            }
        }

        return view('pengembalian.create', compact('pinjamList'));
    }

    public function store(PengembalianRequest $request)
    {
        try {
            $result = $this->fineService->processReturn(
                $request->integer('pinjam_id'),
                $request->input('tanggal_dikembalikan')
            );

            $message = 'Data berhasil disimpan.';
            if (($result->denda ?? 0) > 0) {
                $message .= ' Denda: Rp ' . number_format(($result->denda ?? 0), 0, ',', '.');
            }

            return redirect()->route('pengembalian.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        $pengembalian = Pengembalian::find($id);
        if (!$pengembalian) {
            abort(404);
        }

        $pengembalian->book = Pengembalian::bookFor($pengembalian);
        $pengembalian->anggota = Pengembalian::anggotaFor($pengembalian);
        $pengembalian->pinjam = Pengembalian::pinjamFor($pengembalian);
        $pengembalian->fine = Pengembalian::fineFor($pengembalian);

        $pengembalian->denda = $pengembalian->fine?->denda ?? ($pengembalian->denda ?? 0);

        if (!empty($pengembalian->tanggal_pinjam)) {
            $pengembalian->tanggal_pinjam = \Carbon\Carbon::parse($pengembalian->tanggal_pinjam);
        }
        if (!empty($pengembalian->tanggal_kembali)) {
            $pengembalian->tanggal_kembali = \Carbon\Carbon::parse($pengembalian->tanggal_kembali);
        }
        if (!empty($pengembalian->tanggal_dikembalikan)) {
            $pengembalian->tanggal_dikembalikan = \Carbon\Carbon::parse($pengembalian->tanggal_dikembalikan);
        }

        return view('pengembalian.show', compact('pengembalian'));
    }

    public function destroy($id)
    {
        $pengembalian = Pengembalian::find($id);
        if (!$pengembalian) {
            abort(404);
        }

        $pinjamRow = DB::table('pinjam')->where('id', $pengembalian->pinjam_id)->first();
        if ($pinjamRow) {
            DB::table('pinjam')
                ->where('id', $pengembalian->pinjam_id)
                ->update(['status' => 'dipinjam']);

            $bookId = $pinjamRow->book_id ?? null;
            if ($bookId) {
                $bookRow = DB::table('books')->where('id', $bookId)->first();
                if ($bookRow) {
                    $this->inventoryService->refreshBookStatus($bookRow);
                }
            }
        }

        DB::table('fines')->where('pengembalian_id', $id)->delete();
        DB::table('pengembalian')->where('id', $id)->delete();

        return redirect()->route('pengembalian.index')->with('success', 'Data berhasil dihapus dan status dipinjam dikembalikan.');
    }

    public function fines(Request $request, $anggotaId)
    {
        $anggota = Anggota::find($anggotaId);
        if (!$anggota) {
            abort(404);
        }

        $totalFines = $this->fineService->getTotalFines($anggotaId);

        $historyRows = DB::table('pengembalian')
            ->where('anggota_id', $anggotaId)
            ->orderByDesc('id')
            ->get();

        foreach ($historyRows as $p) {
            $p->book = Pengembalian::bookFor($p);
            $p->fine = Pengembalian::fineFor($p);
            $p->denda = $p->fine?->denda ?? ($p->denda ?? 0);

            if (!empty($p->tanggal_pinjam)) {
                $p->tanggal_pinjam = \Carbon\Carbon::parse($p->tanggal_pinjam);
            }
            if (!empty($p->tanggal_kembali)) {
                $p->tanggal_kembali = \Carbon\Carbon::parse($p->tanggal_kembali);
            }
            if (!empty($p->tanggal_dikembalikan)) {
                $p->tanggal_dikembalikan = \Carbon\Carbon::parse($p->tanggal_dikembalikan);
            }
        }

        return view('pengembalian.fines', compact('anggota', 'totalFines', 'historyRows'));
    }
}
