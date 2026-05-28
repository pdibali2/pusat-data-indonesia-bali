<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anomaly_reviews', function (Blueprint $table) {
            $table->integer('anomaly_reviews_id')->autoIncrement();

            $table->integer('anomaly_id');
            $table->integer('reviewer_id'); // user_id dari tabel user

            // Keputusan: approved | approved_with_note | rejected | revised
            $table->string('decision', 30);

            // Alasan wajib diisi jika approved_with_note atau rejected
            $table->text('justification')->nullable();

            // Catatan tambahan opsional
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('anomaly_id')
                ->references('anomalies_id')
                ->on('anomalies')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('reviewer_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index(['anomaly_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anomaly_reviews');
    }
};