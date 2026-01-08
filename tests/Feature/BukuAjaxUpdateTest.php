<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BukuAjaxUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function createUser(string $role = 'admin'): Pengguna
    {
        return Pengguna::create([
            'nama' => 'Tester',
            'email' => $role.'@example.com',
            'kata_sandi' => 'password',
            'peran' => $role,
        ]);
    }

    public function test_admin_can_update_book_via_json(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $buku = Buku::create([
            'judul' => 'Judul Awal',
            'penulis' => 'Penulis Awal',
            'genre' => 'AI',
            'tahun_terbit' => 2022,
            'stok' => 10,
            'deskripsi' => 'Awal',
        ]);

        $payload = [
            'judul' => 'Judul Baru',
            'penulis' => 'Penulis Baru',
            'kategori' => 'Teknologi',
            'tahun_terbit' => 2025,
            'stok' => 7,
            'deskripsi' => 'Diperbarui',
        ];

        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->put(route('kelola-buku.update', $buku->id), $payload);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.judul', 'Judul Baru')
            ->assertJsonPath('data.penulis', 'Penulis Baru')
            ->assertJsonPath('data.genre', 'Teknologi')
            ->assertJsonPath('data.tahun_terbit', 2025)
            ->assertJsonPath('data.stok', 7);

        $this->assertDatabaseHas('buku', [
            'id' => $buku->id,
            'judul' => 'Judul Baru',
            'penulis' => 'Penulis Baru',
            'genre' => 'Teknologi',
            'tahun_terbit' => 2025,
            'stok' => 7,
            'deskripsi' => 'Diperbarui',
        ]);
    }
}

