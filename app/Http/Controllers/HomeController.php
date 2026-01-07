<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pinjaman;
use App\Models\Buku;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Statistik aktivitas pengguna
        $sedangDipinjam = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'sedang_dipinjam')
            ->count();

        // Hitung hari tersisa untuk pengembalian terdekat
        $pinjamanTerdekat = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'sedang_dipinjam')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->first();

        $hariTersisa = null;
        if ($pinjamanTerdekat && $pinjamanTerdekat->tanggal_jatuh_tempo) {
            $hariTersisa = max(0, Carbon::now()->diffInDays(Carbon::parse($pinjamanTerdekat->tanggal_jatuh_tempo), false));
        }

        // Buku yang telah dibaca bulan ini
        $bukuBulanIni = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'dikembalikan')
            ->whereMonth('tanggal_kembali', Carbon::now()->month)
            ->whereYear('tanggal_kembali', Carbon::now()->year)
            ->count();

        // Genre favorit berdasarkan buku yang dipinjam
        $genreFavorit = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'dikembalikan')
            ->join('buku', 'pinjaman.buku_id', '=', 'buku.id')
            ->selectRaw('buku.genre, COUNT(*) as jumlah')
            ->groupBy('buku.genre')
            ->orderByDesc('jumlah')
            ->first();

        // Rekomendasi KNN berdasarkan buku yang dipinjam
        $rekomendasiBuku = $this->getRekomendasiKNN($user->id);

        // Riwayat peminjaman terbaru
        $riwayatPeminjaman = Pinjaman::where('pengguna_id', $user->id)
            ->with('buku')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('home', [
            'user' => $user,
            'sedangDipinjam' => $sedangDipinjam,
            'hariTersisa' => $hariTersisa,
            'bukuBulanIni' => $bukuBulanIni,
            'genreFavorit' => $genreFavorit ? $genreFavorit->genre : 'Belum ada',
            'rekomendasiBuku' => $rekomendasiBuku,
            'riwayatPeminjaman' => $riwayatPeminjaman,
        ]);
    }

    private function getRekomendasiKNN($penggunaId, $k = 5)
    {
        // Ambil semua peminjaman
        $semuaPinjaman = Pinjaman::with('buku')->get();

        // Ambil buku yang pernah dipinjam user ini
        $bukuUser = Pinjaman::where('pengguna_id', $penggunaId)
            ->pluck('buku_id')
            ->unique()
            ->toArray();

        if (empty($bukuUser)) {
            // Jika user belum pernah pinjam, return buku populer
            return Buku::orderBy('stok', 'desc')
                ->limit(10)
                ->get();
        }

        // Ambil genre dari buku yang dipinjam user
        $genreUser = Buku::whereIn('id', $bukuUser)
            ->pluck('genre')
            ->filter()
            ->unique()
            ->toArray();

        // Cari user lain yang pinjam buku dengan genre yang sama
        $userSerupa = Pinjaman::where('pengguna_id', '!=', $penggunaId)
            ->whereHas('buku', function($q) use ($genreUser) {
                $q->whereIn('genre', $genreUser);
            })
            ->selectRaw('pengguna_id, COUNT(*) as jumlah')
            ->groupBy('pengguna_id')
            ->orderByDesc('jumlah')
            ->limit($k)
            ->pluck('pengguna_id')
            ->toArray();

        if (empty($userSerupa)) {
            // Jika tidak ada user serupa, return buku dengan genre yang sama
            return Buku::whereIn('genre', $genreUser)
                ->whereNotIn('id', $bukuUser)
                ->where('stok', '>', 0)
                ->limit(10)
                ->get();
        }

        // Ambil buku yang dipinjam user serupa tapi belum dipinjam user ini
        $bukuRekomendasi = Pinjaman::whereIn('pengguna_id', $userSerupa)
            ->whereNotIn('buku_id', $bukuUser)
            ->with('buku')
            ->selectRaw('buku_id, COUNT(*) as frekuensi')
            ->groupBy('buku_id')
            ->orderByDesc('frekuensi')
            ->limit(10)
            ->get()
            ->pluck('buku')
            ->filter();

        if ($bukuRekomendasi->isEmpty()) {
            // Fallback: buku dengan genre yang sama
            return Buku::whereIn('genre', $genreUser)
                ->whereNotIn('id', $bukuUser)
                ->where('stok', '>', 0)
                ->limit(10)
                ->get();
        }

        return $bukuRekomendasi;
    }
}
