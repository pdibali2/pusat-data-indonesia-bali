<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan_fitur', function (Blueprint $table) {
            $table->integer('layanan_fitur_id')->autoIncrement();
            $table->integer('layanan_id');
            $table->foreign('layanan_id')
                  ->references('layanan_id')
                  ->on('layanan')
                  ->onDelete('cascade');
            $table->string('nama_fitur', 200);
            $table->boolean('aktif')->default(true);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan_fitur');
    }
};