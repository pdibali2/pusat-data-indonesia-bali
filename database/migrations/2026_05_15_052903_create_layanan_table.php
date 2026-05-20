<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('layanan', function (Blueprint $table) {
            $table->integer('layanan_id')->autoIncrement();

            $table->string('nama_layanan', 200);
            $table->decimal('harga', 15, 2);

            // durasi: angka + tipe (harian|mingguan|bulanan|tahunan|selamanya)
            $table->unsignedInteger('durasi')->default(1);
            $table->enum('durasi_type', ['harian', 'mingguan', 'bulanan', 'tahunan', 'selamanya'])
                  ->default('bulanan');

            $table->enum('status', ['publish', 'pending', 'takedown'])->default('pending');
            $table->boolean('is_popular')->default(false);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->string('thumbnail', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};