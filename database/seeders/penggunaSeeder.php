<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;

class penggunaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createOrUpdateDefaultUser(
            'Admin Perpustakaan',
            'admin@mail.com',
            'password',
            'admin'
        );

        $this->createOrUpdateDefaultUser(
            'Staff Perpustakaan',
            'staff@mail.com',
            'password',
            'staff'
        );
    }

    /**
     * Create or update default user (admin/staff only)
     */
    private function createOrUpdateDefaultUser($nama, $email, $password, $peran)
    {
        // Gunakan updateOrCreate untuk memastikan data selalu ter-update
        Pengguna::updateOrCreate(
            ['email' => $email], // Kondisi pencarian
            [
                'nama' => $nama,
                'email' => $email,
                'kata_sandi' => $password, // Model akan otomatis hash via mutator
                'peran' => $peran,
            ]
        );
    }
}
