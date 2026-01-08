<?php

namespace App\Services;

class KnnRekomendasi
{
    /**
     * Hitung beberapa rekomendasi menggunakan pendekatan K-Nearest Neighbors sederhana.
     * Mengembalikan array dari ['buku_id' => int, 'skor' => float] terurut desc berdasarkan skor.
     *
     * @param int $penggunaId
     * @param \Illuminate\Support\Collection $dataPeminjaman Collection of Pinjaman models
     * @param int $k jumlah tetangga terdekat yang dipertimbangkan
     * @param int $limit jumlah rekomendasi yang dikembalikan
     * @return array
     */
    public function hitungRekomendasi($penggunaId, $dataPeminjaman, $k = 5, $limit = 10): array
    {
        // Hanya gunakan pinjaman yang relevan (yang benar-benar dipinjam/dikembalikan)
        $dataPeminjaman = $dataPeminjaman->filter(function ($r) {
            return in_array($r->status, ['dikembalikan', 'sedang_dipinjam']);
        });

        // Buat map pengguna => set buku yang dipinjam
        $userBukuMap = [];
        foreach ($dataPeminjaman as $row) {
            $uid = $row->pengguna_id;
            $userBukuMap[$uid] = $userBukuMap[$uid] ?? [];
            $userBukuMap[$uid][$row->buku_id] = true;
        }

        $targetSet = isset($userBukuMap[$penggunaId]) ? array_keys($userBukuMap[$penggunaId]) : [];

        // Jika user belum pernah pinjam, fallback ke buku populer (diambil dari frekuensi)
        if (empty($targetSet)) {
            $freq = [];
            foreach ($dataPeminjaman as $row) {
                $freq[$row->buku_id] = ($freq[$row->buku_id] ?? 0) + 1;
            }
            arsort($freq);

            $out = [];
            foreach ($freq as $bukuId => $cnt) {
                $out[] = ['buku_id' => $bukuId, 'skor' => (float) $cnt];
                if (count($out) >= $limit) break;
            }

            return $out;
        }

        // Hitung similarity (Jaccard) antara target dan tiap user lain
        $scores = [];
        foreach ($userBukuMap as $uid => $set) {
            if ($uid == $penggunaId) continue;
            $setKeys = array_keys($set);
            $intersect = count(array_intersect($targetSet, $setKeys));
            $union = count(array_unique(array_merge($targetSet, $setKeys)));
            $sim = $union > 0 ? $intersect / $union : 0;
            if ($sim > 0) {
                $scores[$uid] = $sim;
            }
        }

        // Ambil top-K users
        arsort($scores);
        $topUsers = array_slice($scores, 0, $k, true);

        // Koleksi kandidat buku dari top users yang belum pernah dipinjam target
        $candidateScores = [];
        foreach ($topUsers as $uid => $sim) {
            foreach (array_keys($userBukuMap[$uid] ?? []) as $bukuId) {
                if (in_array($bukuId, $targetSet)) continue;
                // tambahkan bobot berdasarkan similarity; jika beberapa user menyarankan buku yang sama, jumlahkan
                $candidateScores[$bukuId] = ($candidateScores[$bukuId] ?? 0) + $sim;
            }
        }

        // Jika tidak ada kandidat, fallback ke genre-based atau populer: return kosong for caller to handle
        if (empty($candidateScores)) {
            return [];
        }

        // Normalisasi dan urutkan
        arsort($candidateScores);

        $out = [];
        foreach ($candidateScores as $bukuId => $score) {
            $out[] = ['buku_id' => $bukuId, 'skor' => (float) $score];
            if (count($out) >= $limit) break;
        }

        return $out;
    }
}
