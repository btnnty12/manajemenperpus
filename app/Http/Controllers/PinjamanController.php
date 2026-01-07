<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PinjamanController extends Controller
{
    // LIST SEMUA PINJAMAN
    public function index()
    {
        $pinjaman = Pinjaman::with(['pengguna', 'buku'])->get();

        return response()->json($pinjaman);
    }

    // CREATE PINJAMAN
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'pengguna_id' => 'required|exists:pengguna,id',
            'buku_id' => 'required|exists:buku,id',
        ]);

        $pengguna = Pengguna::findOrFail($request->pengguna_id);
        $buku = Buku::findOrFail($request->buku_id);

        // Validasi role (hanya pengguna/staff)
        if (! in_array($pengguna->peran, ['pengguna', 'staff'])) {
            return response()->json(['message' => 'Pengguna tidak berhak meminjam buku'], 403);
        }

        // Validasi stok buku
        if ($buku->stok < 1) {
            return response()->json(['message' => 'Stok buku tidak tersedia'], 400);
        }

        // Cek apakah user sudah meminjam buku ini dan belum dikembalikan
        $existing = Pinjaman::where('pengguna_id', $pengguna->id)
            ->where('buku_id', $buku->id)
            ->where('status', 'sedang_dipinjam')
            ->first();
        if ($existing) {
            return response()->json(['message' => 'Pengguna sudah meminjam buku ini'], 400);
        }

        // Buat peminjaman
        $pinjaman = Pinjaman::create([
            'pengguna_id' => $pengguna->id,
            'buku_id' => $buku->id,
            'status' => 'sedang_dipinjam',
            'tanggal_pinjam' => Carbon::now()->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(7)->toDateString(),
            'denda' => 0,
        ]);

        // Kurangi stok buku
        $buku->decrement('stok');

        return response()->json([
            'message' => 'Buku berhasil dipinjam',
            'data' => $pinjaman,
        ], 201);
    }

    // UPDATE STATUS PINJAMAN
    public function update(Request $request, $id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        $request->validate([
            'status' => 'required|in:wishlist,sedang_dipinjam,dikembalikan',
        ]);

        $oldStatus = $pinjaman->status;
        $newStatus = $request->status;

        // Jika status menjadi dikembalikan, tambahkan stok buku
        if ($oldStatus !== 'dikembalikan' && $newStatus === 'dikembalikan') {
            Buku::where('id', $pinjaman->buku_id)->increment('stok');
        }

        $pinjaman->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Status peminjaman berhasil diperbarui',
            'data' => $pinjaman,
        ]);
    }

    // PENGEMBALIAN BUKU
    public function returnBook($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);

        // Cegah double return
        if ($pinjaman->status === 'dikembalikan') {
            return response()->json(['message' => 'Buku sudah dikembalikan sebelumnya'], 400);
        }

        // Update status & tambahkan stok buku
        $tanggalKembali = Carbon::now();
        $jatuhTempo = $pinjaman->tanggal_jatuh_tempo ? Carbon::parse($pinjaman->tanggal_jatuh_tempo) : $tanggalKembali;
        $telatHari = max(0, $tanggalKembali->diffInDays($jatuhTempo, false));
        $tarifPerHari = 2000;
        $denda = $telatHari * $tarifPerHari;

        $pinjaman->update([
            'status' => 'dikembalikan',
            'tanggal_kembali' => $tanggalKembali->toDateString(),
            'denda' => $denda,
        ]);
        Buku::where('id', $pinjaman->buku_id)->increment('stok');

        return response()->json(['message' => 'Buku berhasil dikembalikan']);
    }
}
