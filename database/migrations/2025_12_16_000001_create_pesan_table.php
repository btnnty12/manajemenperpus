<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengirim_id');
            $table->unsignedBigInteger('penerima_id');
            $table->unsignedBigInteger('reply_to_id')->nullable();
            $table->text('isi');
            $table->enum('status', ['sent', 'confirmed'])->default('sent');
            $table->boolean('dibaca')->default(false);
            $table->timestamps();

            $table->foreign('pengirim_id')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('penerima_id')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('pesan')->onDelete('cascade');

            $table->index(['penerima_id', 'dibaca']);
            $table->index(['pengirim_id']);
            $table->index(['reply_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesan');
    }
};
