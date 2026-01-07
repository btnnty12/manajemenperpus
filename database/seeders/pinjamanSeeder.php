<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class pinjamanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Pinjaman::create([
            'pengguna_id' => 1,
            'buku_id' => 1,
            'status' => 'sedang_dipinjam',
            'tanggal_pinjam' => now()->subDays(5),
            'tanggal_jatuh_tempo' => now()->addDays(9),
        ]);

        \App\Models\Pinjaman::create([
            'pengguna_id' => 2,
            'buku_id' => 2,
            'status' => 'dikembalikan',
            'tanggal_pinjam' => now()->subDays(15),
            'tanggal_jatuh_tempo' => now()->subDays(5),
            'tanggal_kembali' => now()->subDays(3),
        ]);
    }
}
