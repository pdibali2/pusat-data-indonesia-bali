<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klasifikasi', function (Blueprint $table) {
            if (!Schema::hasColumn('klasifikasi', 'icon')) {
                $table->string('icon', 50)->nullable()->after('nama_klasifikasi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('klasifikasi', function (Blueprint $table) {
            if (Schema::hasColumn('klasifikasi', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
