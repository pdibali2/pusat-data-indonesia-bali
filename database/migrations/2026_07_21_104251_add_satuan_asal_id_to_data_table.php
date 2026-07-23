<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * satuan_asal_id: satuan ASLI dari sumber data (satuan_rujukan di Excel),
     * murni untuk audit/tampilan. TIDAK dipakai untuk perhitungan apapun —
     * number_value sudah dinormalisasi manual ke satuan_id (satuan resmi
     * metadata) sebelum diimport.
     */
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->integer('satuan_asal_id')->nullable()->after('satuan_id');

            $table->foreign('satuan_asal_id')
                ->references('satuan_id')
                ->on('satuan')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropForeign(['satuan_asal_id']);
            $table->dropColumn('satuan_asal_id');
        });
    }
};