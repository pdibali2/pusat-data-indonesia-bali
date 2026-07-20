<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('metadata', function (Blueprint $table) {
            $table->json('sub_nama_metadata')->nullable()->after('satuan_data');
            // Contoh isi: {"1": "Jarak Sekolah (m)", "2": "Jarak Sekolah (km)"}
            // key = satuan_id, value = label tampilan
        });
    }

    public function down(): void
    {
        Schema::table('metadata', function (Blueprint $table) {
            $table->dropColumn('sub_nama_metadata');
        });
    }
};