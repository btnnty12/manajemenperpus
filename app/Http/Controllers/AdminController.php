<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                return [
                    'name' => $a->pengguna->nama ?? 'Unknown',
                    'action' => $a->description ?? $a->type,
                    'book' => $a->meta['buku_judul'] ?? ($a->meta['keyword'] ?? ''),
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

        for ($i = 1; $i <= 12; $i++) {
            $start = Carbon::create($currentYear, $i, 1)->startOfMonth();
            $end = Carbon::create($currentYear, $i, 1)->endOfMonth();

            // Gunakan tanggal_pinjam untuk peminjaman
            $peminjam = Pinjaman::whereIn('status', ['sedang_dipinjam', 'dapat_diambil'])
                ->whereNotNull('tanggal_pinjam')
                ->whereBetween('tanggal_pinjam', [$start->toDateString(), $end->toDateString()])
                ->count();

            // Gunakan tanggal_kembali untuk pengembalian
            $pengembalian = Pinjaman::where('status', 'dikembalikan')
                ->whereNotNull('tanggal_kembali')
                ->whereBetween('tanggal_kembali', [$start->toDateString(), $end->toDateString()])
                ->count();

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
                        'name' => $p->pengguna->nama,
                        'action' => $p->status === 'sedang_dipinjam' ? 'Meminjam Buku' : 'Mengembalikan Buku',
                        'book' => $p->buku->judul,
                        'avatar' => 'avatar-1.png',
                        'note' => '',
                        'created_at' => $p->created_at->format('Y-m-d H:i:s'),
                    ];
                })
        );
    }

    public function getRecentLoans(Request $request)
    {
        return response()->json(
            Pinjaman::with(['pengguna', 'buku'])
                ->orderBy('tanggal_pinjam', 'desc')
                ->limit($request->get('limit', 10))
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'judul_buku' => $p->buku->judul,
                        'peminjam' => $p->pengguna->nama,
                        'tanggal_pinjam' => $p->tanggal_pinjam ? $p->tanggal_pinjam->format('d M Y') : '-',
                        'status' => $p->status,
                    ];
                })
        );
    }
}
