<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengguna_id')->nullable();
            $table->string('judul');
            $table->text('pesan');
            $table->enum('tipe', ['info', 'warning', 'success', 'error'])->default('info');
            $table->boolean('dibaca')->default(false);
            $table->string('link')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('pengguna_id')->references('id')->on('pengguna')->onDelete('cascade');
            
            // Index
            $table->index('pengguna_id');
            $table->index('dibaca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};
