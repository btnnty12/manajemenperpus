<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Buku;

class bukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua dummy data dari model dan insert/update ke DB
        $dummy = Buku::dummyData();

        foreach ($dummy as $key => $item) {
            // Normalisasi fields dari dummyData
            $judul = $item['title'] ?? $key;
            $penulis = $item['author'] ?? ($item['penulis'] ?? 'Unknown');
            $genre = $item['genre'] ?? 'Umum';
            $deskripsi = $item['desc'] ?? ($item['deskripsi'] ?? '');
            $tahun = $item['year'] ?? ($item['tahun_terbit'] ?? null);
            $stok = $item['stok'] ?? 1;
            $img = $item['img'] ?? null;

            // Gunakan updateOrCreate agar seeder idempotent
            Buku::updateOrCreate(
                ['judul' => $judul],
                [
                    'penulis' => $penulis,
                    'genre' => $genre,
                    'deskripsi' => $deskripsi,
                    'tahun_terbit' => $tahun,
                    'stok' => $stok,
                    // If img is a remote url leave it as cover path null (or you can set to URL)
                    // We store image filename/path in `cover` column if provided and not an absolute URL
                    'cover' => ($img && (strpos($img, 'http') === 0 || strpos($img, '/') === 0)) ? ltrim($img, '/') : $img,
                ]
            );
        }
    }
}
