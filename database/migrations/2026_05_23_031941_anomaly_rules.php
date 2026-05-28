<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_rules', function (Blueprint $table) {
            $table->integer('anomaly_rules_id')->autoIncrement();

            // Nama aturan: mis. "Kenaikan Ekstrem Tahunan"
            $table->string('name', 150);
            $table->string('code', 50)->unique(); // mis: extreme_increase

            // Tipe aturan: percentage_change | absolute_change | source_conflict | custom
            $table->string('rule_type', 50);

            // Threshold per severity (persentase atau nilai absolut)
            $table->decimal('threshold_low',      10, 4)->default(20);
            $table->decimal('threshold_medium',   10, 4)->default(100);
            $table->decimal('threshold_high',     10, 4)->default(500);
            $table->decimal('threshold_critical', 10, 4)->default(5000);

            // Berlaku untuk metadata_id tertentu (null = semua)
            $table->integer('metadata_id')->nullable();

            // Berlaku untuk frekuensi tertentu (null = semua)
            // Nilai: tahunan | semester | quarter | bulanan
            $table->string('frekuensi', 50)->nullable();

            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('metadata_id')
                ->references('metadata_id')
                ->on('metadata')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_rules');
    }
};