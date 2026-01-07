<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    // LIST BUKU
    public function index()
    {
        return response()->json(Buku::all());
    }

    // CREATE BUKU
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_terbit' => 'required|integer|min:1500|max:2099',
            'stok' => 'required|integer|min:0',
        ]);

        $buku = Buku::create($validated);

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data' => $buku,
        ], 201);
    }

    // DETAIL BUKU
    public function show($id)
    {
        $buku = Buku::findOrFail($id);

        return response()->json($buku);
    }

    // UPDATE BUKU
    public function update(Request $request, $id)
    {
        $buku = Buku::findOrFail($id);

        $validated = $request->validate([
            'judul' => 'sometimes|required|string|max:255',
            'penulis' => 'sometimes|required|string|max:255',
            'genre' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
            'tahun_terbit' => 'sometimes|required|integer|min:1500|max:2099',
            'stok' => 'sometimes|required|integer|min:0',
        ]);

        $buku->update($validated);

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data' => $buku,
        ]);
    }

    // DELETE BUKU
    public function destroy($id)
    {
        $buku = Buku::findOrFail($id);

        // Cek apakah ada pinjaman aktif
        if ($buku->pinjaman()->where('status', 'sedang_dipinjam')->exists()) {
            return response()->json([
                'message' => 'Buku tidak bisa dihapus karena masih dipinjam',
            ], 400);
        }

        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus']);
    }
}
