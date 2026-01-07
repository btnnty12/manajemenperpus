<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;

class DataAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $query = Pengguna::where('peran', 'pengguna');

        // Filter search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by created_at desc (terbaru)
        $anggota = $query->orderBy('created_at', 'desc')->paginate(10);

        // Hitung statistik
        $totalAnggota = Pengguna::where('peran', 'pengguna')->count();
        
        // Hitung anggota aktif (yang punya pinjaman aktif)
        $aktif = \App\Models\Pinjaman::where('status', 'sedang_dipinjam')
            ->distinct('pengguna_id')
            ->count('pengguna_id');
        
        $nonaktif = 0; // Belum ada field status untuk nonaktif
        $anggotaBaru = Pengguna::where('peran', 'pengguna')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Tentukan view berdasarkan role user
        $user = auth()->user();
        $viewName = ($user && $user->peran === 'staff') ? 'staff.data-anggota' : 'data-anggota';
        
        return view($viewName, [
            'anggota' => $anggota,
            'totalAnggota' => $totalAnggota,
            'aktif' => $aktif,
            'nonaktif' => $nonaktif,
            'anggotaBaru' => $anggotaBaru,
        ]);
    }
}

