<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_pencarian', function (Blueprint $table) {
            $table->id(); // Primary Key

            $table->foreignId('pengguna_id')
                ->constrained('pengguna') // relasi ke tabel pengguna
                ->onDelete('cascade')
                ->index();

            $table->string('kata_kunci');
            $table->integer('jumlah_hasil')->default(0);

            $table->string('algorithm')->default('bm');           // bm / kmp / bf
            $table->float('process_time_ms', 10, 5)->nullable();  // durasi eksekusi dalam ms

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_pencarian');
    }
};
