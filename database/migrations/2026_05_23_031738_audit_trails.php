<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->bigInteger('audit_trail_id')->autoIncrement();

            $table->integer('user_id')->nullable(); // nullable untuk sistem/otomatis
            $table->string('table_name', 100);
            $table->string('record_id', 50);        // bisa integer atau string

            // Tipe aksi: created | updated | deleted | approved | rejected | revised | screened
            $table->string('action_type', 30);

            // JSON dari nilai lama & baru
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();

            // Alasan perubahan (wajib untuk update/delete sensitif)
            $table->text('reason')->nullable();

            // IP address & user agent untuk keamanan
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Audit trail TIDAK boleh diupdate/delete — hanya created_at
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index(['table_name', 'record_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};