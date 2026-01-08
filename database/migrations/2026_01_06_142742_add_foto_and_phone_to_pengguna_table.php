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
        Schema::table('pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('pengguna', 'foto')) {
                $table->string('foto')->nullable()->after('email');
            }
            if (!Schema::hasColumn('pengguna', 'phone')) {
                $table->string('phone', 20)->nullable()->after('foto');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            if (Schema::hasColumn('pengguna', 'foto')) {
                $table->dropColumn('foto');
            }
            if (Schema::hasColumn('pengguna', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
