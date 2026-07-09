<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_sessions', 'session_token')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->string('session_token')->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('user_sessions', 'is_active')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_sessions', 'session_token')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->dropColumn('session_token');
            });
        }

        if (Schema::hasColumn('user_sessions', 'is_active')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }
};
