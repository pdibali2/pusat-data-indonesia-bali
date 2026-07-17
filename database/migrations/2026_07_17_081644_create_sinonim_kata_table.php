<?php
// database/migrations/xxxx_xx_xx_create_sinonim_kata_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sinonim_kata', function (Blueprint $table) {
            $table->id();
            $table->string('kata', 100);          // varian kata (termasuk kata dasarnya sendiri)
            $table->string('kata_dasar', 100);     // key pengelompokan — semua sinonim satu grup punya kata_dasar sama
            $table->enum('sumber', ['dataset', 'manual'])->default('dataset');
            $table->timestamps();

            $table->index('kata');
            $table->index('kata_dasar');
            $table->unique(['kata', 'kata_dasar']); // cegah duplikat saat re-import
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinonim_kata');
    }
};