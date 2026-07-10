<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_logins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('cascade');
            $table->string('ip_address');
            $table->string('device_info');
            $table->integer('target_session_id');
            $table->foreign('target_session_id')
                ->references('user_session_id')
                ->on('user_sessions')
                ->onDelete('cascade');
            $table->string('session_token')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('estimated_location')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();

            $table->index(['target_session_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_logins');
    }
};
