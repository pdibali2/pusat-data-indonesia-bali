<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('user_sessions', 'logout_reason')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->string('logout_reason')->nullable()->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('user_sessions', 'logout_reason')) {
            Schema::table('user_sessions', function (Blueprint $table): void {
                $table->dropColumn('logout_reason');
            });
        }
    }
};
