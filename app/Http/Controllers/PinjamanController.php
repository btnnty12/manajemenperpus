<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use App\Models\Activity;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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

        $payload = [
            'pengguna_id' => $pengguna->id,
            'buku_id' => $buku->id,
            'status' => 'sedang_dipinjam',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
            $payload['tanggal_pinjam'] = Carbon::now()->toDateString();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            $payload['tanggal_jatuh_tempo'] = Carbon::now()->addDays(7)->toDateString();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'denda')) {
            $payload['denda'] = 0;
        }
        $pinjaman = Pinjaman::create($payload);

        // Kurangi stok buku
        $buku->decrement('stok');

        // Log aktivitas pinjam buku
        Activity::create([
            'pengguna_id' => $pengguna->id,
            'type' => 'pinjam_buku',
            'description' => "Meminjam buku: {$buku->judul}",
            'meta' => [
                'buku_id' => $buku->id,
                'buku_judul' => $buku->judul,
                'pinjaman_id' => $pinjaman->id,
                'tanggal_pinjam' => $pinjaman->tanggal_pinjam,
                'tanggal_jatuh_tempo' => $pinjaman->tanggal_jatuh_tempo,
            ],
        ]);

        $pesanPinjam = "Buku '{$buku->judul}' berhasil dipinjam.";
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo') && $pinjaman->tanggal_jatuh_tempo) {
            try {
                $pesanPinjam .= " Jatuh tempo: " . Carbon::parse($pinjaman->tanggal_jatuh_tempo)->format('d F Y');
            } catch (\Exception $e) {}
        }
        Notifikasi::create([
            'pengguna_id' => $pengguna->id,
            'judul' => 'Buku Berhasil Dipinjam',
            'pesan' => $pesanPinjam,
            'tipe' => 'success',
            'dibaca' => false,
            'link' => route('pengembalian.index'),
        ]);

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
        // Hitung telat secara benar: hari_kembali > jatuh_tempo => telat
        $telatHari = 0;
        if ($jatuhTempo->lt($tanggalKembali)) {
            $telatHari = $jatuhTempo->diffInDays($tanggalKembali);
        }
        $tarifPerHari = 2000;
        $denda = $telatHari * $tarifPerHari;

        $updatePayload = [
            'status' => 'dikembalikan',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'tanggal_kembali')) {
            $updatePayload['tanggal_kembali'] = $tanggalKembali->toDateString();
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'denda')) {
            $updatePayload['denda'] = $denda;
        }
        $pinjaman->update($updatePayload);
        Buku::where('id', $pinjaman->buku_id)->increment('stok');

        // Log aktivitas kembalikan buku
        $buku = Buku::find($pinjaman->buku_id);
        $judulBuku = $buku ? $buku->judul : 'Buku';
        Activity::create([
            'pengguna_id' => $pinjaman->pengguna_id,
            'type' => 'kembalikan_buku',
            'description' => "Mengembalikan buku: {$judulBuku}",
            'meta' => [
                'buku_id' => $pinjaman->buku_id,
                'buku_judul' => $buku ? $buku->judul : null,
                'pinjaman_id' => $pinjaman->id,
                'tanggal_kembali' => $tanggalKembali->toDateString(),
                'denda' => $denda,
                'telat_hari' => $telatHari,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buku berhasil dikembalikan'
        ]);
    }
}
