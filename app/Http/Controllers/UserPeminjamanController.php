<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pinjaman;
use App\Models\Buku;
use Illuminate\Support\Facades\Schema;

class UserPeminjamanController extends Controller
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

        // Filter status jika ada
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('tanggal_dari') && $request->tanggal_dari) {
            if (Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
                $query->whereDate('tanggal_pinjam', '>=', $request->tanggal_dari);
            } else {
                $query->whereDate('created_at', '>=', $request->tanggal_dari);
            }
        }
        if ($request->has('tanggal_sampai') && $request->tanggal_sampai) {
            if (Schema::hasColumn('pinjaman', 'tanggal_pinjam')) {
                $query->whereDate('tanggal_pinjam', '<=', $request->tanggal_sampai);
            } else {
                $query->whereDate('created_at', '<=', $request->tanggal_sampai);
            }
        }

        $pinjaman = $query->get();

        // Hitung statistik
        $stats = [
            'total' => Pinjaman::where('pengguna_id', $user->id)->count(),
            'sedang_dipinjam' => Pinjaman::where('pengguna_id', $user->id)->where('status', 'sedang_dipinjam')->count(),
            'dikembalikan' => Pinjaman::where('pengguna_id', $user->id)->where('status', 'dikembalikan')->count(),
            'hilang' => Pinjaman::where('pengguna_id', $user->id)->where('status', 'hilang')->count(),
        ];

        return view('peminjaman', [
            'pinjaman' => $pinjaman,
            'stats' => $stats,
            'user' => $user,
        ]);
    }
}
