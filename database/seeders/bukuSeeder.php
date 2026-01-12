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

        // --- Tambahan: insert data buku baru sesuai request ---
        $now = now();

        $data = [
            ['Laskar Pelangi', 'Andrea Hirata', 'Sastra Indonesia', 2005],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Sejarah & Sastra', 1980],
            ['Negeri 5 Menara', 'Ahmad Fuadi', 'Motivasi', 2009],
            ['Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', 'Religi', 2004],
            ['Filosofi Teras', 'Henry Manampiring', 'Psikologi', 2018],
            ['Atomic Habits (Terjemahan)', 'James Clear', 'Pengembangan Diri', 2019],
            ['Sapiens', 'Yuval Noah Harari', 'Sejarah', 2017],
            ['Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Keuangan', 2015],
            ['Pemrograman Web Laravel', 'Eko Kurniawan Khannedy', 'Teknologi', 2022],
            ['Basis Data', 'Abdul Kadir', 'Teknologi', 2018],
            ['Algoritma & Struktur Data', 'Rinaldi Munir', 'Teknologi', 2019],
            ['Sistem Informasi Manajemen', 'Jogiyanto', 'Manajemen', 2017],
            ['Manajemen Perpustakaan', 'Sulistyo Basuki', 'Perpustakaan', 2016],
            ['Metodologi Penelitian', 'Sugiyono', 'Pendidikan', 2020],
            ['Statistika untuk Penelitian', 'Sudjana', 'Pendidikan', 2019],
            ['Pengantar Ilmu Komunikasi', 'Deddy Mulyana', 'Sosial', 2018],
            ['Ilmu Politik', 'Miriam Budiardjo', 'Sosial', 2017],
            ['Hukum Perdata', 'Subekti', 'Hukum', 2016],
            ['Hukum Tata Negara', 'Jimly Asshiddiqie', 'Hukum', 2018],
            ['Akuntansi Dasar', 'Warren Reeve Fess', 'Akuntansi', 2020],
        ];

        $books = [];

        foreach ($data as $index => $item) {
            $books[] = [
                'judul' => $item[0],
                'penulis' => $item[1],
                'genre' => $item[2],
                'deskripsi' => "Buku {$item[0]} karya {$item[1]} yang umum tersedia di perpustakaan Indonesia.",
                'cover' => $index % 2 === 0
                    ? "covers/buku-" . ($index + 1) . ".jpg"
                    : "https://picsum.photos/300/400?random=" . ($index + 1),
                'tahun_terbit' => $item[3],
                'stok' => rand(3, 15),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Gandakan hingga ~100 data (5x) dan beri label edisi
        $finalBooks = [];
        for ($i = 1; $i <= 5; $i++) {
            foreach ($books as $buku) {
                if (count($finalBooks) >= 100) break 2;
                $buku['judul'] .= " (Edisi {$i})";
                $finalBooks[] = $buku;
            }
        }

        // Masukkan ke DB secara idempotent (hindari duplikat berdasarkan judul)
        foreach ($finalBooks as $b) {
            Buku::firstOrCreate(
                ['judul' => $b['judul']],
                [
                    'penulis' => $b['penulis'],
                    'genre' => $b['genre'],
                    'deskripsi' => $b['deskripsi'],
                    'cover' => $b['cover'],
                    'tahun_terbit' => $b['tahun_terbit'],
                    'stok' => $b['stok'],
                ]
            );
        }
    }
}
