<?php

namespace App\Http\Controllers;

use App\Models\Rekomendasi;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    // =========================================
    // GET: Semua rekomendasi
    // =========================================
    public function index()
    {
        $rek = Rekomendasi::with(['pengguna', 'buku'])
            ->orderBy('pengguna_id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rek,
        ]);
    }

    // =========================================
    // POST: Tambah rekomendasi single
    // =========================================
    public function store(Request $request)
    {
        $request->validate([
            'pengguna_id' => 'required|exists:pengguna,id',
            'buku_id' => 'required|exists:buku,id',
            'skor_rekomendasi' => 'nullable|numeric',
        ]);

        $rek = Rekomendasi::updateOrCreate(
            [
                'pengguna_id' => $request->pengguna_id,
                'buku_id' => $request->buku_id,
            ],
            [
                'skor_rekomendasi' => $request->skor_rekomendasi ?? 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi berhasil disimpan',
            'data' => $rek,
        ]);
    }

    // =========================================
    // GET: Detail rekomendasi by ID
    // =========================================
    public function show($id)
    {
        $rek = Rekomendasi::with(['pengguna', 'buku'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rek,
        ]);
    }

    // =========================================
    // PUT: Update skor rekomendasi
    // =========================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'skor_rekomendasi' => 'required|numeric',
        ]);

        $rek = Rekomendasi::findOrFail($id);
        $rek->update([
            'skor_rekomendasi' => $request->skor_rekomendasi,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi diperbarui',
        ]);
    }

    // =========================================
    // DELETE: Hapus rekomendasi
    // =========================================
    public function destroy($id)
    {
        Rekomendasi::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rekomendasi dihapus',
        ]);
    }

    // =========================================
    // GET: Rekomendasi berdasarkan pengguna
    // =========================================
    public function getByUser($pengguna_id)
    {
        $rek = Rekomendasi::with('buku')
            ->where('pengguna_id', $pengguna_id)
            ->orderByDesc('skor_rekomendasi')
            ->get();

        // Jika user belum punya rekomendasi
        if ($rek->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada rekomendasi untuk pengguna ini',
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $rek,
        ]);
    }

    // =========================================================
    // POST: SIMPAN BANYAK REKOMENDASI (Batch dari algoritma KNN)
    // =========================================================
    public function simpanBatch(Request $request)
    {
        $request->validate([
            'pengguna_id' => 'required|exists:pengguna,id',
            'rekomendasi' => 'required|array',
            'rekomendasi.*.buku_id' => 'required|exists:buku,id',
            'rekomendasi.*.skor' => 'required|numeric',
        ]);

        $pengguna_id = $request->pengguna_id;
        $data = $request->rekomendasi;

        // Hapus rekomendasi lama
        Rekomendasi::where('pengguna_id', $pengguna_id)->delete();

        // Siapkan untuk bulk insert
        $insertData = [];
        foreach ($data as $item) {
            $insertData[] = [
                'pengguna_id' => $pengguna_id,
                'buku_id' => $item['buku_id'],
                'skor_rekomendasi' => $item['skor'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Rekomendasi::insert($insertData);

        return response()->json([
            'success' => true,
            'message' => 'Batch rekomendasi berhasil disimpan',
        ]);
    }
}
