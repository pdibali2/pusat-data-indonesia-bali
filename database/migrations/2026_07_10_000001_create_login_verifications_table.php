<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('cascade');
            $table->integer('new_session_id');
            $table->foreign('new_session_id')
                ->references('user_session_id')
                ->on('user_sessions')
                ->onDelete('cascade');
            $table->string('device_type');
            $table->string('browser');
            $table->string('estimated_location')->nullable();
            $table->string('ip_address');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('responded_by_session_id')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['new_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_verifications');
    }
};
