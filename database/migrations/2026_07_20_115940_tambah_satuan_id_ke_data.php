<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->integer('satuan_id')->nullable()->after('number_value');

            $table->foreign('satuan_id')
                ->references('satuan_id')
                ->on('satuan')
                ->onDelete('set null')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropForeign(['satuan_id']);
            $table->dropColumn('satuan_id');
        });
    }
};