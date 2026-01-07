<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengguna_id');
            $table->string('type'); // login, pinjam_buku, kembalikan_buku, cari_buku
            $table->text('description')->nullable();
            $table->json('meta')->nullable(); // untuk menyimpan data tambahan seperti buku_id, keyword, dll
            $table->timestamps();

            $table->foreign('pengguna_id')->references('id')->on('pengguna')->onDelete('cascade');
            $table->index('pengguna_id');
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
