<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('penulis');
            $table->string('genre')->nullable();
            $table->text('deskripsi')->nullable();

            $table->unsignedSmallInteger('tahun_terbit')->nullable();

            $table->unsignedInteger('stok')->default(0);

            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('judul');
            $table->index('penulis');

            // $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
