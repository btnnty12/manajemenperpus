<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Pengguna;
use App\Models\Pinjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_displays_recommendations_via_knn()
    {
        // Buat pengguna dan beberapa buku
        $user = Pengguna::factory()->create();
        $other = Pengguna::factory()->create();
        $another = Pengguna::factory()->create();

        $common = ['penulis' => 'Penulis X', 'genre' => 'Fiksi', 'deskripsi' => 'desc', 'tahun_terbit' => 2020];
        $b1 = Buku::create(array_merge($common, ['judul' => 'Buku A', 'stok' => 5]));
        $b2 = Buku::create(array_merge($common, ['judul' => 'Buku B', 'stok' => 5]));
        $b3 = Buku::create(array_merge($common, ['judul' => 'Buku C', 'stok' => 5]));
        $b4 = Buku::create(array_merge($common, ['judul' => 'Buku D', 'stok' => 5]));

        // Riwayat peminjaman: user meminjam b1,b2; other meminjam b1,b2,b3; another meminjam b2,b3,b4
        Pinjaman::create(['pengguna_id' => $user->id, 'buku_id' => $b1->id, 'status' => 'dikembalikan']);
        Pinjaman::create(['pengguna_id' => $user->id, 'buku_id' => $b2->id, 'status' => 'dikembalikan']);

        Pinjaman::create(['pengguna_id' => $other->id, 'buku_id' => $b1->id, 'status' => 'dikembalikan']);
        Pinjaman::create(['pengguna_id' => $other->id, 'buku_id' => $b2->id, 'status' => 'dikembalikan']);
        Pinjaman::create(['pengguna_id' => $other->id, 'buku_id' => $b3->id, 'status' => 'dikembalikan']);

        Pinjaman::create(['pengguna_id' => $another->id, 'buku_id' => $b2->id, 'status' => 'dikembalikan']);
        Pinjaman::create(['pengguna_id' => $another->id, 'buku_id' => $b3->id, 'status' => 'dikembalikan']);
        Pinjaman::create(['pengguna_id' => $another->id, 'buku_id' => $b4->id, 'status' => 'dikembalikan']);

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
        $response->assertViewHas('rekomendasiBuku');

        $rekom = $response->original->getData()['rekomendasiBuku'];
        $this->assertNotEmpty($rekom);

        // Pastikan koleksi berisi model Buku dan stok tertera
        $this->assertInstanceOf(\App\Models\Buku::class, $rekom->first());
        $this->assertGreaterThan(0, $rekom->first()->stok);

        // Pastikan rekomendasi mengandung buku yang belum dipinjam user (b3 atau b4)
        $ids = $rekom->pluck('id')->toArray();
        $this->assertTrue(in_array($b3->id, $ids) || in_array($b4->id, $ids));
    }
}
