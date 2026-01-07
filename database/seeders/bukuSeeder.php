<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class bukuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Buku::create([
            'judul' => 'Pemrograman Laravel',
            'penulis' => 'Rizki A',
            'genre' => 'Teknologi',
            'deskripsi' => 'Buku untuk belajar framework Laravel.',
            'tahun_terbit' => 2023,
        ]);

        \App\Models\Buku::create([
            'judul' => 'Algoritma & Struktur Data',
            'penulis' => 'Dewi L',
            'genre' => 'Informatika',
            'deskripsi' => 'Dasar logika pemrograman.',
            'tahun_terbit' => 2022,
        ]);
    }
}
