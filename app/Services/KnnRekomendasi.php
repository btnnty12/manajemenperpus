<?php

namespace App\Services;

class KnnRecommendation
{
    public function hitungRekomendasi($penggunaId, $dataPeminjaman, $k = 3)
    {
        // 1. Ambil daftar buku yang pernah dipinjam pengguna tersebut
        $userHistory = $dataPeminjaman->where('pengguna_id', $penggunaId);

        // 2. Cari user lain yang memiliki riwayat mirip (similarity)
        $similarUsers = $dataPeminjaman->where('pengguna_id', '!=', $penggunaId);

        // 3. Hitung jarak/similaritas sederhana (contoh: jumlah buku yang sama)
        $similarityScores = [];
        foreach ($similarUsers as $row) {
            $score = $userHistory->where('buku_id', $row->buku_id)->count();
            $similarityScores[$row->pengguna_id] = ($similarityScores[$row->pengguna_id] ?? 0) + $score;
        }

        // 4. Ambil K user terdekat (nilai similarity tertinggi)
        arsort($similarityScores);
        $topUsers = array_slice($similarityScores, 0, $k, true);

        // 5. Ambil buku yang mereka pinjam tapi belum pernah dipinjam oleh pengguna
        $rekomendasiBuku = $dataPeminjaman
            ->whereIn('pengguna_id', array_keys($topUsers))
            ->whereNotIn('buku_id', $userHistory->pluck('buku_id'))
            ->first();

        return [
            'buku_id' => $rekomendasiBuku->buku_id ?? null,
            'skor' => max($topUsers) ?? 0,
        ];
    }
}
