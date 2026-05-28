<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {

            // ── produsen_id ─────────────────────────────────────
            if (!Schema::hasColumn('data', 'produsen_id')) {

                $table->integer('produsen_id')
                      ->nullable()
                      ->after('status');

                $table->foreign('produsen_id')
                    ->references('produsen_id')
                    ->on('produsen_data')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            // ── workflow_status ────────────────────────────────
            if (!Schema::hasColumn('data', 'workflow_status')) {

                $table->string('workflow_status', 30)
                      ->default('draft')
                      ->after('status');

                $table->index('workflow_status');
            }

            // ── reviewer_note ──────────────────────────────────
            if (!Schema::hasColumn('data', 'reviewer_note')) {

                $table->text('reviewer_note')
                      ->nullable()
                      ->after('workflow_status');
            }

            // ── reviewed_by ────────────────────────────────────
            if (!Schema::hasColumn('data', 'reviewed_by')) {

                $table->integer('reviewed_by')
                      ->nullable()
                      ->after('reviewer_note');

                $table->foreign('reviewed_by')
                    ->references('user_id')
                    ->on('user')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            // ── reviewed_at ────────────────────────────────────
            if (!Schema::hasColumn('data', 'reviewed_at')) {

                $table->timestamp('reviewed_at')
                      ->nullable()
                      ->after('reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {

            // Drop foreign keys
            try {
                $table->dropForeign(['produsen_id']);
            } catch (\Throwable $e) {}

            try {
                $table->dropForeign(['reviewed_by']);
            } catch (\Throwable $e) {}

            // Drop index
            try {
                $table->dropIndex(['workflow_status']);
            } catch (\Throwable $e) {}

            // Drop columns jika ada
            $columns = [
                'produsen_id',
                'workflow_status',
                'reviewer_note',
                'reviewed_by',
                'reviewed_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('data', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};