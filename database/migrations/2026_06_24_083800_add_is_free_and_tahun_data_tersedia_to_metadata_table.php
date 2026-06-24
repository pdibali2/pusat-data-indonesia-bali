<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata', function (Blueprint $table) {
            // Default 0 = premium, data lama otomatis premium semua
            // Admin tinggal toggle mana yang mau dijadikan free
            $table->tinyInteger('is_free')->default(0)->after('status');

            // Nullable, bisa diisi manual atau nanti via sync command
            $table->string('tahun_data_tersedia', 20)->nullable()->after('is_free');
        });
    }

    public function down(): void
    {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn(['is_free', 'tahun_data_tersedia']);
        });
    }
};