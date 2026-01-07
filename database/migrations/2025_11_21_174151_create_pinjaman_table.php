<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengguna_id');
            $table->unsignedBigInteger('buku_id');
            $table->enum('status', ['sedang_dipinjam', 'dikembalikan', 'hilang'])->default('sedang_dipinjam');
            $table->date('tanggal_pinjam')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->date('tanggal_kembali')->nullable();
            $table->decimal('denda', 10, 2)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('pengguna_id')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('buku_id')->references('id')->on('buku')->onDelete('cascade');

            // Index
            $table->index('pengguna_id');
            $table->index('buku_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
