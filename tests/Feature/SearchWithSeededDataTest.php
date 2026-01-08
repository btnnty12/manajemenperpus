<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;

class SearchWithSeededDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_seeded_book()
    {
        // Seed buku data
        $this->seed(\Database\Seeders\bukuSeeder::class);

        // Create and authenticate a user
        $user = Pengguna::factory()->create(['peran' => 'pengguna']);

        $response = $this->actingAs($user)->getJson('/api/search?q=Python');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertArrayHasKey('results', $response->json('data'));

        $results = $response->json('data.results');
        $this->assertNotEmpty($results, 'Expected at least one search result for "Python"');

        $titles = array_map(fn($r) => $r['judul'], $results);
        $this->assertTrue(collect($titles)->contains('Python Book'));
    }
}
