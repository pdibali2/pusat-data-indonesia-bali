<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomalies', function (Blueprint $table) {
            $table->integer('anomalies_id')->autoIncrement();

            // Referensi ke data yang anomali
            $table->string('table_name', 100)->default('data');
            
            // Tipe & klasifikasi anomali
            $table->string('anomaly_type', 50);
            // Nilai: extreme_increase | extreme_decrease | source_conflict | unreasonable_value
            
            $table->string('severity', 20)->default('low');
            // Nilai: low | medium | high | critical

            // Nilai pembanding
            $table->decimal('previous_value', 15, 4)->nullable();
            $table->decimal('current_value', 15, 4)->nullable();
            $table->decimal('percentage_change', 15, 4)->nullable();

            // Pesan deskriptif
            $table->text('message');
            
            // Workflow status
            $table->string('status', 30)->default('warning');
            // Nilai: warning | under_review | approved | approved_with_note | rejected | revised

            $table->timestamp('detected_at')->useCurrent();
            $table->timestamps();
            
            $table->integer('id'); // Referensi ke id di tabel data
            $table->foreign('id')
                ->references('id')
                ->on('data')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index(['id', 'status']);
            $table->index(['severity', 'status']);
            $table->index('detected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomalies');
    }
};