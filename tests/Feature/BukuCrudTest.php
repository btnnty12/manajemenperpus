<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BukuCrudTest extends TestCase
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

    public function test_admin_can_create_book(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $response = $this->post(route('kelola-buku.store'), [
            'judul' => 'Buku Admin',
            'penulis' => 'Penulis Admin',
            'kategori' => 'Teknologi',
            'tahun_terbit' => 2024,
            'stok' => 5,
            'deskripsi' => 'Deskripsi admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Admin',
            'penulis' => 'Penulis Admin',
            'genre' => 'Teknologi',
            'tahun_terbit' => 2024,
            'stok' => 5,
            'deskripsi' => 'Deskripsi admin',
        ]);
    }

    public function test_staff_can_create_book(): void
    {
        $staff = $this->createUser('staff');
        $this->actingAs($staff);

        $response = $this->post(route('staff.kelola-buku.store'), [
            'judul' => 'Buku Staff',
            'penulis' => 'Penulis Staff',
            'kategori' => 'Pemrograman',
            'tahun_terbit' => 2023,
            'stok' => 3,
            'deskripsi' => 'Deskripsi staff',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('buku', [
            'judul' => 'Buku Staff',
            'penulis' => 'Penulis Staff',
            'genre' => 'Pemrograman',
            'tahun_terbit' => 2023,
            'stok' => 3,
            'deskripsi' => 'Deskripsi staff',
        ]);
    }

    public function test_admin_can_update_and_delete_book(): void
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

        $resShow = $this->get(route('kelola-buku.show', $buku->id));
        $resShow->assertOk()->assertJsonFragment(['judul' => 'Judul Awal']);

        $resUpdate = $this->put(route('kelola-buku.update', $buku->id), [
            'judul' => 'Judul Baru',
            'penulis' => 'Penulis Baru',
            'kategori' => 'Teknologi',
            'tahun_terbit' => 2025,
            'stok' => 7,
            'deskripsi' => 'Diperbarui',
        ]);

        $resUpdate->assertRedirect();
        $resUpdate->assertSessionHas('success');

        $this->assertDatabaseHas('buku', [
            'id' => $buku->id,
            'judul' => 'Judul Baru',
            'penulis' => 'Penulis Baru',
            'genre' => 'Teknologi',
            'tahun_terbit' => 2025,
            'stok' => 7,
            'deskripsi' => 'Diperbarui',
        ]);

        $resDelete = $this->delete(route('kelola-buku.destroy', $buku->id));
        $resDelete->assertRedirect();
        $resDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('buku', ['id' => $buku->id]);
    }

    public function test_staff_can_import_csv(): void
    {
        $staff = $this->createUser('staff');
        $this->actingAs($staff);

        $content = implode("\n", [
            "\xEF\xBB\xBFJudul;Penulis;Kategori;Tahun Terbit;Stok;Deskripsi",
            'Machine Learning;Andrew Ng;Teknologi;2018;12;Pembelajaran mesin',
            'Cyber Security;Kevin Mitnick;Keamanan;2019;6;Keamanan siber',
        ]);
        $file = UploadedFile::fake()->createWithContent('import.csv', $content);

        $response = $this->post(route('staff.kelola-buku.import.process'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('buku', [
            'judul' => 'Machine Learning',
            'penulis' => 'Andrew Ng',
            'genre' => 'Teknologi',
            'tahun_terbit' => 2018,
            'stok' => 12,
        ]);
        $this->assertDatabaseHas('buku', [
            'judul' => 'Cyber Security',
            'penulis' => 'Kevin Mitnick',
            'genre' => 'Keamanan',
            'tahun_terbit' => 2019,
            'stok' => 6,
        ]);
    }
}
