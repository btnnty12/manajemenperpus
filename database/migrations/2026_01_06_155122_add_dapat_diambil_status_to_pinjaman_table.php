<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah enum status untuk menambahkan status 'dapat_diambil' dan 'menunggu_approval'
        // Hanya jalankan ALTER TABLE untuk database yang mendukung ENUM/MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pinjaman MODIFY COLUMN status ENUM('menunggu_approval', 'dapat_diambil', 'sedang_dipinjam', 'dikembalikan', 'hilang') DEFAULT 'menunggu_approval'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum sebelumnya jika DB mendukung
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE pinjaman MODIFY COLUMN status ENUM('sedang_dipinjam', 'dikembalikan', 'hilang') DEFAULT 'sedang_dipinjam'");
        }
    }
};
