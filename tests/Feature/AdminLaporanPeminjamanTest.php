<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLaporanPeminjamanTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $role = 'admin'): Pengguna
    {
        return Pengguna::create([
            'nama' => ucfirst($role),
            'email' => $role.'@example.com',
            'kata_sandi' => 'password',
            'peran' => $role,
        ]);
    }

    public function test_admin_laporan_menampilkan_dua_peminjaman(): void
    {
        $admin = $this->createUser('admin');
        $borrower = $this->createUser('pengguna');

        $b1 = Buku::create([
            'judul' => 'Algoritma Data',
            'penulis' => 'D. Knuth',
            'genre' => 'Teknologi',
            'tahun_terbit' => 1973,
            'stok' => 1,
            'deskripsi' => 'Classic',
        ]);
        $b2 = Buku::create([
            'judul' => 'Keamanan Jaringan',
            'penulis' => 'K. Mitnick',
            'genre' => 'Keamanan',
            'tahun_terbit' => 2015,
            'stok' => 1,
            'deskripsi' => 'Security',
        ]);

        Pinjaman::create([
            'pengguna_id' => $borrower->id,
            'buku_id' => $b1->id,
            'status' => 'sedang_dipinjam',
            'tanggal_pinjam' => now()->toDateString(),
        ]);
        Pinjaman::create([
            'pengguna_id' => $borrower->id,
            'buku_id' => $b2->id,
            'status' => 'dapat_diambil',
            'tanggal_pinjam' => now()->toDateString(),
        ]);

        $this->actingAs($admin);
        $response = $this->get(route('laporan-peminjaman'));
        $response->assertOk();
        $response->assertSee('Algoritma Data');
        $response->assertSee('Keamanan Jaringan');
    }
}

