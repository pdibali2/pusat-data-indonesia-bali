<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            if (! Schema::hasColumn('layanan', 'category')) {
                $table->enum('category', ['personal', 'organisasi'])->nullable()->after('audience_type');
            }

            if (! Schema::hasColumn('layanan', 'max_templates')) {
                $table->integer('max_templates')->nullable()->default(10)->after('max_concurrent_sessions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            if (Schema::hasColumn('layanan', 'max_templates')) {
                $table->dropColumn('max_templates');
            }

            if (Schema::hasColumn('layanan', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
