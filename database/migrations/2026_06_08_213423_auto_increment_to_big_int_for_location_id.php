<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Langsung ubah saja, tidak ada FK yang perlu di-drop
        Schema::table('location', function (Blueprint $table) {
            $table->bigInteger('location_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('location', function (Blueprint $table) {
            $table->bigInteger('location_id')->autoIncrement()->change();
        });
    }
};