<?php

namespace App\Http\Controllers;

use App\Models\Pengembalian;
use App\Models\Pinjam;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PengembalianController extends Controller
{
    public function index()
    {
        $pengembalian = Pengembalian::with(['anggota', 'book', 'pinjam'])->get();
        return view('pengembalian.index', compact('pengembalian'));
    }

    public function create()
    {
        $pinjamList = Pinjam::with(['anggota', 'book'])->where('status', 'dipinjam')->get();
        return view('pengembalian.create', compact('pinjamList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pinjam_id' => 'required|exists:pinjam,id',
            'tanggal_dikembalikan' => 'required|date',
        ], [
            'required' => 'data tidak lengkap',
        ]);

        $pinjam = Pinjam::findOrFail($validated['pinjam_id']);

        // Calculate denda if late
        $tanggalKembali = Carbon::parse($pinjam->tanggal_kembali);
        $tanggalDikembalikan = Carbon::parse($validated['tanggal_dikembalikan']);
        $denda = 0;

        if ($tanggalDikembalikan->gt($tanggalKembali)) {
            $daysLate = $tanggalDikembalikan->diffInDays($tanggalKembali);
            $denda = $daysLate * 5000; // Rp 5000 per day late
        }

        // Create pengembalian record
        Pengembalian::create([
            'pinjam_id' => $pinjam->id,
            'anggota_id' => $pinjam->anggota_id,
            'book_id' => $pinjam->book_id,
            'tanggal_pinjam' => $pinjam->tanggal_pinjam,
            'tanggal_kembali' => $pinjam->tanggal_kembali,
            'tanggal_dikembalikan' => $validated['tanggal_dikembalikan'],
            'denda' => $denda,
        ]);

        // Update pinjam status
        $pinjam->update(['status' => 'dikembalikan']);

        $message = $denda > 0 
            ? 'data berhasil disimpan. Denda: Rp ' . number_format($denda, 0, ',', '.') 
            : 'data berhasil disimpan';

        return redirect()->route('pengembalian.index')->with('success', $message);
    }

    public function show($id)
    {
        $pengembalian = Pengembalian::with(['anggota', 'book', 'pinjam'])->findOrFail($id);
        return view('pengembalian.show', compact('pengembalian'));
    }

    public function destroy($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        
        // Revert pinjam status back to dipinjam
        $pinjam = Pinjam::findOrFail($pengembalian->pinjam_id);
        $pinjam->update(['status' => 'dipinjam']);
        
        $pengembalian->delete();
        
        return redirect()->route('pengembalian.index')->with('success', 'data berhasil disimpan');
    }
}
