<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $check = DB::table('pengguna')->where('email', $email)->exists();

        if (! $check) {
            DB::table('pengguna')->insert([
                'nama' => $nama,
                'email' => $email,
                'kata_sandi' => Hash::make($password),
                'peran' => $peran,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('pengguna')
                ->where('email', $email)
                ->update([
                    'nama' => $nama,
                    'kata_sandi' => Hash::make($password),
                    'peran' => $peran,
                    'updated_at' => now(),
                ]);
        }
    }
}
