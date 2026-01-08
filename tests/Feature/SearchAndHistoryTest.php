<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Pengguna;
use App\Models\LogPencarian;

class SearchAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_bm_search_finds_cyber_book_and_logs_history()
    {
        $this->seed(\Database\Seeders\bukuSeeder::class);

        $user = Pengguna::factory()->create(['peran' => 'pengguna']);

        $response = $this->actingAs($user)->getJson('/api/search?q=cyber&algo=bm');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $titles = array_map(fn($r) => $r['judul'], $response->json('data.results'));
        $this->assertTrue(collect($titles)->contains('Cyber Security'));

        // Check that the search log was created
        $this->assertDatabaseHas('log_pencarian', [
            'pengguna_id' => $user->id,
            'kata_kunci' => 'cyber'
        ]);
    }

    public function test_history_endpoints_work_and_clear()
    {
        $this->seed(\Database\Seeders\bukuSeeder::class);

        $user = Pengguna::factory()->create(['peran' => 'pengguna']);

        // perform searches to create history
        $this->actingAs($user)->getJson('/api/search?q=cyber');
        $this->actingAs($user)->getJson('/api/search?q=python');

        $res = $this->actingAs($user)->getJson('/api/riwayat-pencarian');
        $res->assertStatus(200);
        $data = $res->json('data');
        $this->assertNotEmpty($data);

        // Clear all
        $this->actingAs($user)->deleteJson('/api/riwayat-pencarian')->assertStatus(200);
        $res2 = $this->actingAs($user)->getJson('/api/riwayat-pencarian');
        $this->assertEmpty($res2->json('data'));
    }
}
