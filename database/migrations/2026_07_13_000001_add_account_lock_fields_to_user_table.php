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
        Schema::table('user', function (Blueprint $table) {
            if (! Schema::hasColumn('user', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('block');
            }

            if (! Schema::hasColumn('user', 'unlock_token')) {
                $table->string('unlock_token', 128)->nullable()->after('locked_at');
            }

            if (! Schema::hasColumn('user', 'unlock_token_expires_at')) {
                $table->timestamp('unlock_token_expires_at')->nullable()->after('unlock_token');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'unlock_token_expires_at')) {
                $table->dropColumn('unlock_token_expires_at');
            }

            if (Schema::hasColumn('user', 'unlock_token')) {
                $table->dropColumn('unlock_token');
            }

            if (Schema::hasColumn('user', 'locked_at')) {
                $table->dropColumn('locked_at');
            }
        });
    }
};
