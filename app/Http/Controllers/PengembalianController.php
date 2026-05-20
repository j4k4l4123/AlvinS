<?php

namespace App\Http\Controllers;

use App\Http\Requests\PengembalianRequest;
use App\Models\Anggota;
use App\Models\Pengembalian;
use App\Models\Pinjam;
use App\Services\FineService;
use App\Services\InventoryService;
use Illuminate\Http\Request;

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
        $query = Pengembalian::with(['anggota', 'book', 'pinjam', 'fine']);

        if ($request->filled('search')) {
            $search = strtolower($request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('book', function ($qb) use ($search) {
                    $qb->whereRaw('LOWER(judul) LIKE ?', ['%' . $search . '%']);
                })->orWhereHas('anggota', function ($qa) use ($search) {
                    $qa->whereRaw('LOWER(nama) LIKE ?', ['%' . $search . '%']);
                });
            });
        }

        $pengembalian = $query->latest()->paginate(10)->withQueryString();
        $pinjamAktif = Pinjam::with(['anggota', 'book'])->where('status', 'dipinjam')->get();

        return view('pengembalian.index', compact('pengembalian', 'pinjamAktif'));
    }

    public function create()
    {
        $pinjamList = Pinjam::with(['anggota', 'book'])->where('status', 'dipinjam')->get();

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
        $pengembalian = Pengembalian::with(['anggota', 'book', 'pinjam', 'fine'])->findOrFail($id);

        return view('pengembalian.show', compact('pengembalian'));
    }

    public function destroy($id)
    {
        $pengembalian = Pengembalian::with('fine')->findOrFail($id);
        $pinjam = Pinjam::with('book')->findOrFail($pengembalian->pinjam_id);

        $pinjam->update(['status' => 'dipinjam']);
        if ($pinjam->book) {
            $this->inventoryService->refreshBookStatus($pinjam->book->fresh());
        }
        $pengembalian->fine()?->delete();
        $pengembalian->delete();

        return redirect()->route('pengembalian.index')->with('success', 'Data berhasil dihapus dan status dipinjam dikembalikan.');
    }

    public function fines(Request $request, $anggotaId)
    {
        $anggota = Anggota::findOrFail($anggotaId);
        $totalFines = $this->fineService->getTotalFines($anggotaId);
        $history = Pengembalian::with(['book', 'fine'])->where('anggota_id', $anggotaId)->latest()->get();

        return view('pengembalian.fines', compact('anggota', 'totalFines', 'history'));
    }
}
