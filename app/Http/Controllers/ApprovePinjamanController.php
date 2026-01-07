<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Buku;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class ApprovePinjamanController extends Controller
{
    /**
     * Approve peminjaman (ubah status dari menunggu_approval ke dapat_diambil)
     */
    public function approve($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);
        
        // Pastikan status adalah menunggu_approval
        if ($pinjaman->status !== 'menunggu_approval') {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak dapat diapprove karena status bukan menunggu approval'
            ], 400);
        }

        // Update status menjadi dapat_diambil dan kurangi stok buku
        $pinjaman->update([
            'status' => 'dapat_diambil'
        ]);
        
        // Kurangi stok buku karena sudah diapprove
        $buku = Buku::find($pinjaman->buku_id);
        if ($buku && $buku->stok > 0) {
            $buku->decrement('stok');
        }

        // Buat notifikasi untuk user
        Notifikasi::create([
            'pengguna_id' => $pinjaman->pengguna_id,
            'judul' => 'Peminjaman Disetujui',
            'pesan' => "Peminjaman buku '{$pinjaman->buku->judul}' telah disetujui. Buku dapat diambil sekarang.",
            'tipe' => 'success',
            'dibaca' => false,
            'link' => route('pengembalian.index'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil disetujui'
        ]);
    }

    /**
     * Reject peminjaman (ubah status menjadi ditolak atau hapus)
     */
    public function reject($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);
        
        if ($pinjaman->status !== 'menunggu_approval') {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman tidak dapat ditolak karena status bukan menunggu approval'
            ], 400);
        }

        // Hapus peminjaman atau ubah status menjadi ditolak
        $buku = $pinjaman->buku;
        $judulBuku = $buku->judul;
        
        // Buat notifikasi sebelum hapus
        Notifikasi::create([
            'pengguna_id' => $pinjaman->pengguna_id,
            'judul' => 'Peminjaman Ditolak',
            'pesan' => "Maaf, peminjaman buku '{$judulBuku}' telah ditolak.",
            'tipe' => 'error',
            'dibaca' => false,
            'link' => route('pengembalian.index'),
        ]);

        // Hapus peminjaman
        $pinjaman->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil ditolak'
        ]);
    }

    /**
     * Konfirmasi buku sudah diambil (ubah status dari dapat_diambil ke sedang_dipinjam)
     */
    public function confirmTaken($id)
    {
        $pinjaman = Pinjaman::findOrFail($id);
        
        if ($pinjaman->status !== 'dapat_diambil') {
            return response()->json([
                'success' => false,
                'message' => 'Status peminjaman bukan dapat_diambil'
            ], 400);
        }

        // Update status menjadi sedang_dipinjam
        $pinjaman->update([
            'status' => 'sedang_dipinjam'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate. Buku sedang dipinjam.'
        ]);
    }
}
