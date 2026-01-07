<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaUserTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): Pengguna
    {
        $admin = Pengguna::create([
            'nama' => 'Admin',
            'email' => 'admin@example.com',
            'kata_sandi' => 'password',
            'peran' => 'admin',
        ]);
        $this->actingAs($admin);
        return $admin;
    }

    public function test_admin_can_add_pengguna_role(): void
    {
        $this->actingAsAdmin();

        $res = $this->post(route('kelola-user.store'), [
            'nama' => 'User A',
            'email' => 'usera@example.com',
            'password' => 'secret123',
            'peran' => 'pengguna',
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['message' => 'Pengguna berhasil ditambahkan']);
        $this->assertDatabaseHas('pengguna', [
            'email' => 'usera@example.com',
            'peran' => 'pengguna',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user = Pengguna::create([
            'nama' => 'Staff Old',
            'email' => 'staffold@example.com',
            'kata_sandi' => 'password',
            'peran' => 'staff',
        ]);

        $res = $this->put("/api/pengguna/{$user->id}", [
            'nama' => 'Staff New',
            'peran' => 'admin',
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['message' => 'Data pengguna berhasil diperbarui']);
        $this->assertDatabaseHas('pengguna', [
            'id' => $user->id,
            'nama' => 'Staff New',
            'peran' => 'admin',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = $this->actingAsAdmin();
        $user = Pengguna::create([
            'nama' => 'User Delete',
            'email' => 'ud@example.com',
            'kata_sandi' => 'password',
            'peran' => 'pengguna',
        ]);

        $res = $this->delete("/api/pengguna/{$user->id}");
        $res->assertStatus(200);
        $res->assertJsonFragment(['message' => 'Pengguna berhasil dihapus']);
        $this->assertDatabaseMissing('pengguna', ['id' => $user->id]);
    }

    public function test_stats_endpoint_returns_counts(): void
    {
        $this->actingAsAdmin();
        Pengguna::create([
            'nama' => 'Admin 2',
            'email' => 'admin2@example.com',
            'kata_sandi' => 'password',
            'peran' => 'admin',
        ]);
        Pengguna::create([
            'nama' => 'Staff 1',
            'email' => 'staff1@example.com',
            'kata_sandi' => 'password',
            'peran' => 'staff',
        ]);
        Pengguna::create([
            'nama' => 'Pengguna 1',
            'email' => 'pengguna1@example.com',
            'kata_sandi' => 'password',
            'peran' => 'pengguna',
        ]);

        $res = $this->get('/api/pengguna/stats');
        $res->assertStatus(200);
        $json = $res->json();
        $this->assertArrayHasKey('total', $json);
        $this->assertArrayHasKey('admin', $json);
        $this->assertArrayHasKey('staff', $json);
        $this->assertArrayHasKey('pengguna', $json);
        $this->assertGreaterThanOrEqual(1, $json['admin']);
        $this->assertGreaterThanOrEqual(1, $json['staff']);
        $this->assertGreaterThanOrEqual(1, $json['pengguna']);
    }

    public function test_admin_can_add_staff_role(): void
    {
        $this->actingAsAdmin();

        $res = $this->post(route('kelola-user.store'), [
            'nama' => 'Staff B',
            'email' => 'staffb@example.com',
            'password' => 'secret123',
            'peran' => 'staff',
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['message' => 'Pengguna berhasil ditambahkan']);
        $this->assertDatabaseHas('pengguna', [
            'email' => 'staffb@example.com',
            'peran' => 'staff',
        ]);
    }

    public function test_admin_can_add_admin_role(): void
    {
        $this->actingAsAdmin();

        $res = $this->post(route('kelola-user.store'), [
            'nama' => 'Admin C',
            'email' => 'adminc@example.com',
            'password' => 'secret123',
            'peran' => 'admin',
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['message' => 'Pengguna berhasil ditambahkan']);
        $this->assertDatabaseHas('pengguna', [
            'email' => 'adminc@example.com',
            'peran' => 'admin',
        ]);
    }
}
