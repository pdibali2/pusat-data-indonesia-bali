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
        // Add status to user table
        if (!Schema::hasColumn('user', 'status')) {
            Schema::table('user', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('block');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to group table
        if (!Schema::hasColumn('group', 'status')) {
            Schema::table('group', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('title');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to produsen_data table
        if (!Schema::hasColumn('produsen_data', 'status')) {
            Schema::table('produsen_data', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('alamat');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to rujukan table
        if (!Schema::hasColumn('rujukan', 'status')) {
            Schema::table('rujukan', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('produsen_id');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to klasifikasi table
        if (!Schema::hasColumn('klasifikasi', 'status')) {
            Schema::table('klasifikasi', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('nama_klasifikasi');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to time table
        if (!Schema::hasColumn('time', 'status')) {
            Schema::table('time', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('month');
                // 1 = active, 0 = inactive
            });
        }

        // Add status to location table
        if (!Schema::hasColumn('location', 'status')) {
            Schema::table('location', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('nama_wilayah');
                // 1 = active, 0 = inactive
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('group', function (Blueprint $table) {
            if (Schema::hasColumn('group', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('produsen_data', function (Blueprint $table) {
            if (Schema::hasColumn('produsen_data', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('rujukan', function (Blueprint $table) {
            if (Schema::hasColumn('rujukan', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('klasifikasi', function (Blueprint $table) {
            if (Schema::hasColumn('klasifikasi', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('time', function (Blueprint $table) {
            if (Schema::hasColumn('time', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('location', function (Blueprint $table) {
            if (Schema::hasColumn('location', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
