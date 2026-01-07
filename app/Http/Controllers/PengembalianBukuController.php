<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pinjaman;
use App\Models\Buku;
use Carbon\Carbon;

class PengembalianBukuController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Pinjaman::where('pengguna_id', $user->id)
            ->with('buku')
            ->orderBy('created_at', 'desc');

        // Filter search
        if ($request->has('search') && $request->search) {
            $query->whereHas('buku', function($q) use ($request) {
                $q->where('judul', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('penulis', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Filter kategori
        if ($request->has('kategori') && $request->kategori) {
            $query->whereHas('buku', function($q) use ($request) {
                $q->where('genre', $request->kategori);
            });
        }

        // Filter status jika ada
        if ($request->has('status') && $request->status) {
            if ($request->status === 'Terlambat') {
                $query->where('status', 'sedang_dipinjam')
                    ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now());
            } elseif ($request->status === 'Sedang Dipinjam') {
                $query->where('status', 'sedang_dipinjam')
                    ->whereDate('tanggal_jatuh_tempo', '>=', Carbon::now());
            } elseif ($request->status === 'Dapat Diambil') {
                $query->where('status', 'dapat_diambil');
            } elseif ($request->status === 'Menunggu Approval') {
                $query->where('status', 'menunggu_approval');
            } elseif ($request->status === 'Selesai') {
                $query->where('status', 'dikembalikan');
            }
        }

        $pinjaman = $query->get();

        // Hitung statistik
        $total = Pinjaman::where('pengguna_id', $user->id)->count();
        $terlambat = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'sedang_dipinjam')
            ->whereDate('tanggal_jatuh_tempo', '<', Carbon::now())
            ->count();
        $sedangDipinjam = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'sedang_dipinjam')
            ->whereDate('tanggal_jatuh_tempo', '>=', Carbon::now())
            ->count();
        $dapatDiambil = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'dapat_diambil')
            ->count();
        $dikembalikan = Pinjaman::where('pengguna_id', $user->id)
            ->where('status', 'dikembalikan')
            ->count();

        $stats = [
            'total' => $total,
            'terlambat' => $terlambat,
            'sedang_dipinjam' => $sedangDipinjam,
            'dapat_diambil' => $dapatDiambil,
            'dikembalikan' => $dikembalikan,
        ];

        return view('index', [
            'pinjaman' => $pinjaman,
            'stats' => $stats,
            'user' => $user,
        ]);
    }
}
