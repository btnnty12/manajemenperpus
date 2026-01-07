<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    // Menampilkan semua pengguna
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 9); // default 9 data per halaman

        $pengguna = Pengguna::orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Data pengguna berhasil diambil',
            'data' => $pengguna->items(),
            'pagination' => [
                'current_page' => $pengguna->currentPage(),
                'last_page' => $pengguna->lastPage(),
                'per_page' => $pengguna->perPage(),
                'total' => $pengguna->total(),
            ],
        ]);
    }

    // Menampilkan detail pengguna berdasarkan ID
    public function show($id)
    {
        $pengguna = Pengguna::findOrFail($id);

        return response()->json($pengguna);
    }

    // Menambah pengguna baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:pengguna,email',
            'password' => 'required|min:6',
            'peran' => 'nullable|in:admin,pengguna,staff',
        ]);

        $pengguna = Pengguna::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'kata_sandi' => $request->password, // Mutator akan otomatis hash
            'peran' => $request->peran ?? 'pengguna',
        ]);

        return response()->json([
            'message' => 'Pengguna berhasil ditambahkan',
            'data' => $pengguna,
        ]);
    }

    // Update data pengguna
    public function update(Request $request, $id)
    {
        $pengguna = Pengguna::findOrFail($id);

        $pengguna->update([
            'nama' => $request->nama ?? $pengguna->nama,
            'email' => $request->email ?? $pengguna->email,
            'peran' => $request->peran ?? $pengguna->peran,
        ]);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui',
            'data' => $pengguna,
        ]);
    }

    // Menghapus pengguna
    public function destroy($id)
    {
        Pengguna::findOrFail($id)->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }

    // Statistik pengguna per role
    public function stats()
    {
        $total = Pengguna::count();
        $admin = Pengguna::where('peran', 'admin')->count();
        $staff = Pengguna::where('peran', 'staff')->count();
        $pengguna = Pengguna::where('peran', 'pengguna')->count();

        return response()->json([
            'total' => $total,
            'admin' => $admin,
            'staff' => $staff,
            'pengguna' => $pengguna,
        ]);
    }
}
