<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalBuku = Buku::sum('stok');
        $totalUser = Pengguna::where('peran', '!=', 'admin')->count();
        // Anggap semua yang belum "dikembalikan" masih aktif
        $totalPinjamanAktif = Pinjaman::where('status', '!=', 'dikembalikan')->count();

        $chartData = $this->getChartData();

        // Ambil aktivitas pengguna terbaru dari Activity model
        $activities = Activity::with('pengguna')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($a) {
                // Pastikan meta selalu berbentuk array agar akses index aman
                $meta = is_array($a->meta)
                    ? $a->meta
                    : (is_string($a->meta) ? json_decode($a->meta, true) ?: [] : []);

                $book = $meta['buku_judul'] ?? ($meta['keyword'] ?? '');

                return [
                    'name' => $a->pengguna->nama ?? 'Unknown',
                    'action' => $a->description ?? $a->type,
                    'book' => $book,
                    'avatar' => 'avatar-1.png',
                    'note' => '',
                    'created_at' => $a->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $recentLoans = Pinjaman::with(['pengguna', 'buku'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin', compact('totalBuku', 'totalUser', 'totalPinjamanAktif', 'chartData', 'activities', 'recentLoans'));
    }

    public function getStats(Request $request)
    {
        return response()->json([
            'total_buku' => Buku::sum('stok'),
            'total_user' => Pengguna::where('peran', '!=', 'admin')->count(),
            'total_pinjaman_aktif' => Pinjaman::where('status', 'sedang_dipinjam')->count(),
        ]);
    }

    public function getChartData()
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
            'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $currentYear = Carbon::now()->year;
        $data = [];

        $hasTanggalPinjam = Schema::hasColumn('pinjaman', 'tanggal_pinjam');
        $hasTanggalKembali = Schema::hasColumn('pinjaman', 'tanggal_kembali');

        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($currentYear, $i, 1)->startOfMonth();
            $end = Carbon::create($currentYear, $i, 1)->endOfMonth();

            // Gunakan tanggal_pinjam untuk peminjaman
            $peminjamQuery = Pinjaman::whereIn('status', ['sedang_dipinjam', 'dapat_diambil']);
            if ($hasTanggalPinjam) {
                $peminjamQuery->whereNotNull('tanggal_pinjam')
                    ->whereBetween('tanggal_pinjam', [$start->toDateString(), $end->toDateString()]);
            } else {
                $peminjamQuery->whereBetween('created_at', [$start, $end]);
            }
            $peminjam = $peminjamQuery->count();

            // Gunakan tanggal_kembali untuk pengembalian
            $pengembalianQuery = Pinjaman::where('status', 'dikembalikan');
            if ($hasTanggalKembali) {
                $pengembalianQuery->whereNotNull('tanggal_kembali')
                    ->whereBetween('tanggal_kembali', [$start->toDateString(), $end->toDateString()]);
            } else {
                $pengembalianQuery->whereBetween('updated_at', [$start, $end]);
            }
            $pengembalian = $pengembalianQuery->count();

            $data[] = [
                'month' => $months[$i - 1],
                'peminjam' => $peminjam,
                'pengembalian' => $pengembalian,
            ];
        }

        return $data;
    }

    public function getActivities(Request $request)
    {
        return response()->json(
            Pinjaman::with(['pengguna', 'buku'])
                ->orderBy('created_at', 'desc')
                ->limit($request->get('limit', 10))
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'name' => optional($p->pengguna)->nama,
                        'action' => $p->status === 'sedang_dipinjam' ? 'Meminjam Buku' : 'Mengembalikan Buku',
                        'book' => optional($p->buku)->judul,
                        'avatar' => 'avatar-1.png',
                        'note' => '',
                        'created_at' => $p->created_at->format('Y-m-d H:i:s'),
                    ];
                })
        );
    }

    public function getRecentLoans(Request $request)
    {
        $hasTanggalPinjam = Schema::hasColumn('pinjaman', 'tanggal_pinjam');
        $query = Pinjaman::with(['pengguna', 'buku']);
        if ($hasTanggalPinjam) {
            $query->orderBy('tanggal_pinjam', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return response()->json(
            $query
                ->limit($request->get('limit', 10))
                ->get()
                ->map(function ($p) use ($hasTanggalPinjam) {
                    return [
                        'id' => $p->id,
                        'judul_buku' => optional($p->buku)->judul,
                        'peminjam' => optional($p->pengguna)->nama,
                        'tanggal_pinjam' => $hasTanggalPinjam && $p->tanggal_pinjam ? \Carbon\Carbon::parse($p->tanggal_pinjam)->format('d M Y') : ($p->created_at ? $p->created_at->format('d M Y') : '-'),
                        'status' => $p->status,
                    ];
                })
        );
    }

    public function laporanPeminjaman()
    {
        $pinjaman = Pinjaman::with(['pengguna', 'buku'])->orderBy('created_at', 'desc')->get();
        $totalDipinjam = Pinjaman::count();
        $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
        
        $hasTanggalJatuhTempo = Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo');
        $hasDenda = Schema::hasColumn('pinjaman', 'denda');
        
        $terlambat = 0;
        if ($hasTanggalJatuhTempo) {
            try {
                $terlambat = Pinjaman::where('status', 'sedang_dipinjam')
                    ->whereNotNull('tanggal_jatuh_tempo')
                    ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now()->toDateString())
                    ->count();
            } catch (\Exception $e) {
                $terlambat = 0;
            }
        }
        
        $totalDenda = 0;
        if ($hasDenda) {
            try {
                $totalDenda = Pinjaman::whereNotNull('denda')->sum('denda') ?? 0;
            } catch (\Exception $e) {
                $totalDenda = 0;
            }
        }
        
        return view('laporan-peminjaman', compact(
            'pinjaman',
            'totalDipinjam',
            'sedangDipinjam',
            'terlambat',
            'totalDenda',
            'hasTanggalJatuhTempo',
            'hasDenda'
        ));
    }
}
