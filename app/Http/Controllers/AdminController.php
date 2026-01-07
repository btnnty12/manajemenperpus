<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalBuku = Buku::sum('stok');
        $totalUser = Pengguna::where('peran', '!=', 'admin')->count();
        // Anggap semua yang belum "dikembalikan" masih aktif
        $totalPinjamanAktif = Pinjaman::where('status', '!=', 'dikembalikan')->count();

        $chartData = $this->getChartData();

        $activities = Pinjaman::with(['pengguna', 'buku'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                $action = 'Meminjam Buku';
                $note = '';
                
                if ($p->status === 'menunggu_approval') {
                    $action = 'Menunggu Konfirmasi Peminjaman';
                } elseif ($p->status === 'dapat_diambil') {
                    $action = 'Buku Dapat Diambil';
                } elseif ($p->status === 'dikembalikan') {
                    $action = 'Mengembalikan Buku';
                    if ($p->denda && $p->denda > 0) {
                        $note = 'Denda: Rp ' . number_format($p->denda, 0, ',', '.');
                    }
                } elseif ($p->status === 'sedang_dipinjam') {
                    $tanggalJatuhTempo = $p->tanggal_jatuh_tempo ? Carbon::parse($p->tanggal_jatuh_tempo) : null;
                    if ($tanggalJatuhTempo && Carbon::now()->gt($tanggalJatuhTempo)) {
                        $hariTelat = Carbon::now()->diffInDays($tanggalJatuhTempo);
                        $note = 'Telat ' . $hariTelat . ' hari';
                    }
                }
                
                return [
                    'name' => $p->pengguna->nama ?? 'Unknown',
                    'action' => $action,
                    'book' => $p->buku->judul ?? 'Unknown',
                    'avatar' => 'avatar-1.png',
                    'note' => $note,
                    'created_at' => $p->created_at->format('Y-m-d H:i:s'),
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
        $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli',
                   'Agustus','September','Oktober','November','Desember'];

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