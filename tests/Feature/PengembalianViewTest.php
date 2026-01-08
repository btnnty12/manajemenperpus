<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class PengembalianViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengembalian_view_shows_user_pinjaman_and_stats()
    {
        $user = Pengguna::factory()->create();

        $common = ['penulis' => 'Pengarang', 'genre' => 'Ilmu', 'deskripsi' => 'desc', 'tahun_terbit' => 2021];
        $b1 = Buku::create(array_merge($common, ['judul' => 'Buku Telat', 'stok' => 2]));
        $b2 = Buku::create(array_merge($common, ['judul' => 'Buku Aktif', 'stok' => 2]));
        $b3 = Buku::create(array_merge($common, ['judul' => 'Buku Diambil', 'stok' => 2]));

        // Telat: sedang_dipinjam with past due date
        Pinjaman::create([
            'pengguna_id' => $user->id,
            'buku_id' => $b1->id,
            'status' => 'sedang_dipinjam',
            'tanggal_pinjam' => Carbon::now()->subDays(10),
            'tanggal_jatuh_tempo' => Carbon::now()->subDays(2),
        ]);

        // Aktif: sedang_dipinjam with future due date
        Pinjaman::create([
            'pengguna_id' => $user->id,
            'buku_id' => $b2->id,
            'status' => 'sedang_dipinjam',
            'tanggal_pinjam' => Carbon::now()->subDays(3),
            'tanggal_jatuh_tempo' => Carbon::now()->addDays(5),
        ]);

        // Gunakan status yang kompatibel dengan SQLite test DB (sedang_dipinjam/dikembalikan/hilang)
        Pinjaman::create([
            'pengguna_id' => $user->id,
            'buku_id' => $b3->id,
            'status' => 'dikembalikan',
        ]);

        $response = $this->actingAs($user)->get(route('pengembalian.index'));

        $response->assertStatus(200);
        $response->assertViewHas('pinjaman');
        $response->assertViewHas('stats');

        $data = $response->original->getData();
        $this->assertCount(3, $data['pinjaman']);
        $this->assertEquals(1, $data['stats']['terlambat']);
        $this->assertArrayHasKey('dapat_diambil', $data['stats']);
        $this->assertEquals(0, $data['stats']['dapat_diambil']);
        $this->assertEquals(1, $data['stats']['dikembalikan']);
        $this->assertEquals(1, $data['stats']['sedang_dipinjam'] + $data['stats']['dapat_diambil']);
        $this->assertEquals(3, $data['stats']['total']);

        $response->assertSee('Buku Telat');
        $response->assertSee('Buku Aktif');
        $response->assertSee('Buku Diambil');

        // Test status filter 'Terlambat' works
        $resp2 = $this->actingAs($user)->get(route('pengembalian.index', ['status' => 'Terlambat']));
        $resp2->assertStatus(200);
        $data2 = $resp2->original->getData();
        $this->assertCount(1, $data2['pinjaman']);
        $this->assertStringContainsString('Buku Telat', $resp2->getContent());
    }
}
