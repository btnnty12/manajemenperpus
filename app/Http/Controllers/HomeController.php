<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pinjaman;
use App\Models\Buku;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

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

        // Hitung hari tersisa untuk pengembalian terdekat (defensif jika kolom tidak tersedia)
        $hariTersisa = null;
        if (Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            try {
                $pinjamanTerdekat = Pinjaman::where('pengguna_id', $user->id)
                    ->where('status', 'sedang_dipinjam')
                    ->whereNotNull('tanggal_jatuh_tempo')
                    ->orderBy('tanggal_jatuh_tempo', 'asc')
                    ->first();
                
                if ($pinjamanTerdekat && $pinjamanTerdekat->tanggal_jatuh_tempo) {
                    $hariTersisa = max(0, Carbon::now()->diffInDays(Carbon::parse($pinjamanTerdekat->tanggal_jatuh_tempo), false));
                }
            } catch (\Exception $e) {
                $hariTersisa = null;
            }
        }

        // Buku yang telah dibaca bulan ini
        $bukuBulanIniQuery = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'dikembalikan');
        if (Schema::hasColumn('pinjaman', 'tanggal_kembali')) {
            $bukuBulanIniQuery->whereMonth('tanggal_kembali', Carbon::now()->month)
                ->whereYear('tanggal_kembali', Carbon::now()->year);
        } else {
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            $bukuBulanIniQuery->whereBetween('updated_at', [$start, $end]);
        }
        $bukuBulanIni = $bukuBulanIniQuery->count();

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
        // Caching ringkas untuk mengurangi hit perhitungan berulang
        $cacheKey = "rekomendasi:pengguna:{$penggunaId}";
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return Buku::whereIn('id', $cached)->where('stok', '>', 0)->get();
        }

        // Ambil hanya peminjaman yang relevan (dikembalikan atau sedang dipinjam)
        $semuaPinjaman = Pinjaman::whereIn('status', ['dikembalikan', 'sedang_dipinjam'])->get();

        // Gunakan service KNN
        $knn = new \App\Services\KnnRekomendasi();
        $rekomList = $knn->hitungRekomendasi($penggunaId, $semuaPinjaman, $k, 10);

        // Jika service tidak mengembalikan rekomendasi, fallback ke genre/populer
        if (empty($rekomList)) {
            $bukuUser = Pinjaman::where('pengguna_id', $penggunaId)
                ->pluck('buku_id')
                ->unique()
                ->toArray();

            $genreUser = Buku::whereIn('id', $bukuUser)
                ->pluck('genre')
                ->filter()
                ->unique()
                ->toArray();

            if (!empty($genreUser)) {
                $fallback = Buku::whereIn('genre', $genreUser)
                    ->whereNotIn('id', $bukuUser)
                    ->where('stok', '>', 0)
                    ->limit(10)
                    ->get();
            } else {
                $fallback = Buku::orderByDesc('stok')->limit(10)->get();
            }

            // Cache fallback IDs for short period
            cache()->put($cacheKey, $fallback->pluck('id')->toArray(), now()->addMinutes(30));
            return $fallback;
        }

        $bukuIds = array_column($rekomList, 'buku_id');

        // Ambil model buku dan urutkan berdasarkan skor rekomendasi dari service
        $bukuModels = Buku::whereIn('id', $bukuIds)->get()->keyBy('id');
        $ordered = [];
        foreach ($bukuIds as $id) {
            if (isset($bukuModels[$id]) && ($bukuModels[$id]->stok ?? 0) > 0) {
                $ordered[] = $bukuModels[$id];
            }
        }

        // Cache IDs
        cache()->put($cacheKey, collect($ordered)->pluck('id')->toArray(), now()->addMinutes(30));

        return collect($ordered);
    }
}
