<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Buku;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class LaporanPeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pinjaman::with(['pengguna', 'buku']);

        // Filter status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('pengguna_id', $request->user_id);
        }

        // Filter tanggal dari
        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            if (Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
                $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
            } else {
                $query->whereDate('created_at', '>=', $request->tanggal_dari);
            }
        }

        // Filter tanggal sampai
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            if (Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
                $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
            } else {
                $query->whereDate('created_at', '<=', $request->tanggal_sampai);
            }
        }

        $pinjaman = $query->orderBy('created_at', 'desc')->get();
        
        // Hitung statistik
        $totalDipinjam = Pinjaman::count();
        $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
        $menungguApproval = Pinjaman::where('status', 'menunggu_approval')->count();
        $dapatDiambil = Pinjaman::where('status', 'dapat_diambil')->count();
        $terlambatQuery = Pinjaman::where('status', 'sedang_dipinjam');
        if (Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            $terlambatQuery->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now()->toDateString());
        } else {
            $terlambatQuery->whereRaw('1 = 0');
        }
        $terlambat = $terlambatQuery->count();
        $dikembalikan = Pinjaman::where('status', 'dikembalikan')->count();
        if (Schema::hasColumn('pinjaman', 'denda')) {
            $totalDenda = Pinjaman::whereNotNull('denda')->sum('denda') ?? 0;
        } else {
            $totalDenda = 0;
        }

        // Tentukan view berdasarkan role user
        $user = auth()->user();
        $viewName = ($user && $user->peran === 'staff') ? 'staff.laporan-peminjaman' : 'laporan-peminjaman';
        
        return view($viewName, compact(
            'pinjaman',
            'totalDipinjam',
            'sedangDipinjam',
            'menungguApproval',
            'dapatDiambil',
            'terlambat',
            'dikembalikan',
            'totalDenda'
        ));
    }

    // API endpoint untuk mengambil statistik peminjaman (dipakai oleh frontend untuk sinkronisasi)
    public function stats()
    {
        $totalDipinjam = Pinjaman::count();
        $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
        $menungguApproval = Pinjaman::where('status', 'menunggu_approval')->count();
        $dapatDiambil = Pinjaman::where('status', 'dapat_diambil')->count();
        $terlambatQuery = Pinjaman::where('status', 'sedang_dipinjam');
        if (\Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'tanggal_jatuh_tempo')) {
            $terlambatQuery->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '<', \Carbon\Carbon::now()->toDateString());
        } else {
            $terlambatQuery->whereRaw('1 = 0');
        }
        $terlambat = $terlambatQuery->count();
        $dikembalikan = Pinjaman::where('status', 'dikembalikan')->count();
        $totalDenda = \Illuminate\Support\Facades\Schema::hasColumn('pinjaman', 'denda') ? Pinjaman::whereNotNull('denda')->sum('denda') ?? 0 : 0;

        return response()->json([
            'total' => $totalDipinjam,
            'sedang_dipinjam' => $sedangDipinjam,
            'menunggu_approval' => $menungguApproval,
            'dapat_diambil' => $dapatDiambil,
            'terlambat' => $terlambat,
            'dikembalikan' => $dikembalikan,
            'total_denda' => $totalDenda,
        ]);
    }
    }
}
