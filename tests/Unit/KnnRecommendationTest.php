<?php

namespace Tests\Unit;

use App\Services\KnnRecommendation;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class KnnRecommendationTest extends TestCase
{
    public function test_simple_knn_recommendation()
    {
        // Buat data pinjaman sederhana sebagai Collection of objects
        $rows = collect([
            (object)['pengguna_id' => 1, 'buku_id' => 1],
            (object)['pengguna_id' => 1, 'buku_id' => 2],

            (object)['pengguna_id' => 2, 'buku_id' => 1],
            (object)['pengguna_id' => 2, 'buku_id' => 2],
            (object)['pengguna_id' => 2, 'buku_id' => 3],

            (object)['pengguna_id' => 3, 'buku_id' => 2],
            (object)['pengguna_id' => 3, 'buku_id' => 3],
            (object)['pengguna_id' => 3, 'buku_id' => 4],

            (object)['pengguna_id' => 4, 'buku_id' => 5],
        ]);

        $knn = new KnnRecommendation();

        $result = $knn->hitungRekomendasi(1, $rows, 2, 5);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Buku 3 diharapkan muncul sebagai rekomendasi dengan skor > 0
        $first = $result[0];
        $this->assertArrayHasKey('buku_id', $first);
        $this->assertArrayHasKey('skor', $first);
        $this->assertEquals(3, $first['buku_id']);
        $this->assertGreaterThan(0, $first['skor']);
    }
}
