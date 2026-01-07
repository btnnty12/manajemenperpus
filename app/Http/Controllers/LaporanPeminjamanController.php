<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Buku;
use Carbon\Carbon;

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
            $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
        }

        // Filter tanggal sampai
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
        }

        $pinjaman = $query->orderBy('created_at', 'desc')->get();
        
        // Hitung statistik
        $totalDipinjam = Pinjaman::count();
        $sedangDipinjam = Pinjaman::where('status', 'sedang_dipinjam')->count();
        $menungguApproval = Pinjaman::where('status', 'menunggu_approval')->count();
        $dapatDiambil = Pinjaman::where('status', 'dapat_diambil')->count();
        $terlambat = Pinjaman::where('status', 'sedang_dipinjam')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now()->toDateString())
            ->count();
        $dikembalikan = Pinjaman::where('status', 'dikembalikan')->count();
        $totalDenda = Pinjaman::whereNotNull('denda')->sum('denda') ?? 0;

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
}
