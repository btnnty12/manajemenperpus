<?php

namespace Tests\Feature;

use App\Models\Buku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_matches_and_snippet()
    {
        // Create books
        Buku::create([
            'judul' => 'Pengantar Pendidikan',
            'penulis' => 'Budi',
            'genre' => 'Pendidikan',
            'deskripsi' => 'Buku tentang pendidikan modern',
            'tahun_terbit' => 2020,
            'stok' => 5,
        ]);

        Buku::create([
            'judul' => 'Sejarah Pendidikan',
            'penulis' => 'Siti',
            'genre' => 'Sejarah',
            'deskripsi' => 'Sejarah perkembangan pendidikan di Indonesia',
            'tahun_terbit' => 2018,
            'stok' => 2,
        ]);

        $user = \App\Models\Pengguna::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/search?q=pendidikan&algo=kmp&case=1');
        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'data' => ['results', 'pagination', 'process_time_ms']]);

        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertGreaterThanOrEqual(1, $json['data']['pagination']['total']);

        $result = $json['data']['results'][0];
        $this->assertArrayHasKey('matches', $result);
        $this->assertArrayHasKey('judul', $result['matches']);
        $this->assertArrayHasKey('snippet', $result['matches']['judul']);
    }

    public function test_search_tokenized_order_independent()
    {
        Buku::create([
            'judul' => 'Pengantar Pendidikan',
            'penulis' => 'Budi',
            'genre' => 'Pendidikan',
            'deskripsi' => 'Buku tentang pendidikan modern',
            'tahun_terbit' => 2020,
            'stok' => 3,
        ]);

        $user = \App\Models\Pengguna::factory()->create();

        // Query dengan kata yang terbalik urutannya
        $response = $this->actingAs($user)->getJson('/api/search?q=pendidikan+pengantar&algo=bm&case=1');
        $response->assertStatus(200);
        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertGreaterThanOrEqual(1, $json['data']['pagination']['total']);
        $this->assertStringContainsString('Pengantar Pendidikan', $json['data']['results'][0]['judul']);
    }
}
