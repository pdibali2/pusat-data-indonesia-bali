<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->integer('user_session_id')->autoIncrement();
            $table->integer('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('cascade');

            $table->string('session_id')->nullable();
            $table->string('session_token')->nullable()->unique();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (!Schema::hasColumn('user_sessions', 'session_token')) {
            Schema::table('user_sessions', function (Blueprint $table) {
                $table->string('session_token')->nullable()->unique();
            });
        }

        if (!Schema::hasColumn('user_sessions', 'is_active')) {
            Schema::table('user_sessions', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
