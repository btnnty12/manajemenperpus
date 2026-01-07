<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use App\Models\Pinjaman;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    // Ambil notifikasi untuk user yang sedang login
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['notifikasi' => []]);
        }

        $query = Notifikasi::where('pengguna_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Jika hanya ingin yang belum dibaca
        if ($request->has('unread_only') && $request->unread_only) {
            $query->where('dibaca', false);
        }

        $notifikasi = $query->limit(20)->get();

        return response()->json([
            'notifikasi' => $notifikasi,
            'unread_count' => Notifikasi::where('pengguna_id', $user->id)->where('dibaca', false)->count(),
        ]);
    }

    // Tandai notifikasi sebagai sudah dibaca
    public function markAsRead($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);

        // Pastikan notifikasi milik user yang sedang login
        if ($notifikasi->pengguna_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notifikasi->update(['dibaca' => true]);

        return response()->json(['message' => 'Notifikasi ditandai sebagai sudah dibaca']);
    }

    // Tandai semua sebagai sudah dibaca
    public function markAllAsRead()
    {
        Notifikasi::where('pengguna_id', Auth::id())
            ->where('dibaca', false)
            ->update(['dibaca' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai sebagai sudah dibaca']);
    }

    // Buat notifikasi otomatis berdasarkan peminjaman
    public static function createFromPinjaman(Pinjaman $pinjaman, $tipe = 'info')
    {
        // Load relasi buku jika belum dimuat
        if (! $pinjaman->relationLoaded('buku')) {
            $pinjaman->load('buku');
        }

        $judul = '';
        $pesan = '';
        $link = null;

        switch ($tipe) {
            case 'success':
                $judul = 'Peminjaman Berhasil';
                $pesan = "Buku \"{$pinjaman->buku->judul}\" berhasil dipinjam. Jatuh tempo: ".Carbon::parse($pinjaman->tanggal_jatuh_tempo)->format('d M Y');
                $link = '/pengembalian-buku';
                break;
            case 'warning':
                $judul = 'Peringatan Jatuh Tempo';
                $hariTersisa = Carbon::now()->diffInDays(Carbon::parse($pinjaman->tanggal_jatuh_tempo), false);
                if ($hariTersisa < 0) {
                    $pesan = "Buku \"{$pinjaman->buku->judul}\" terlambat ".abs($hariTersisa).' hari. Segera kembalikan!';
                } else {
                    $pesan = "Buku \"{$pinjaman->buku->judul}\" harus dikembalikan dalam {$hariTersisa} hari lagi.";
                }
                $link = '/pengembalian-buku';
                break;
            case 'info':
                $judul = 'Pengembalian Berhasil';
                $pesan = "Buku \"{$pinjaman->buku->judul}\" telah dikembalikan.";
                if ($pinjaman->denda > 0) {
                    $pesan .= ' Denda: Rp '.number_format($pinjaman->denda, 0, ',', '.');
                }
                $link = '/pengembalian-buku';
                break;
        }

        return Notifikasi::create([
            'pengguna_id' => $pinjaman->pengguna_id,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
        ]);
    }

    // Buat notifikasi untuk admin/staff
    public static function createForAdmin($judul, $pesan, $tipe = 'info', $link = null)
    {
        // Buat notifikasi untuk semua admin
        $admins = \App\Models\Pengguna::where('peran', 'admin')->get();

        foreach ($admins as $admin) {
            Notifikasi::create([
                'pengguna_id' => $admin->id,
                'judul' => $judul,
                'pesan' => $pesan,
                'tipe' => $tipe,
                'link' => $link,
            ]);
        }
    }
}
