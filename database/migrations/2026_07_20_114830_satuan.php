<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satuan', function (Blueprint $table) {
            $table->integer('satuan_id')->autoIncrement();
            $table->string('nama_satuan', 100)->unique();   // "Meter", "Kilometer", "Gram", "Kilogram"
            $table->string('simbol', 50)->nullable();       // "m", "km", "g", "kg" — untuk tampilan singkat
            $table->decimal('nilai_konversi', 30, 8);         // nilai relatif terhadap satuan dasar (m=1, km=1000, dst)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satuan');
    }
};