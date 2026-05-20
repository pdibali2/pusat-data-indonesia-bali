<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klasifikasi', function (Blueprint $table) {
            $table->integer('klasifikasi_id')->autoIncrement();
            $table->string('nama_klasifikasi', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klasifikasi');
    }
};