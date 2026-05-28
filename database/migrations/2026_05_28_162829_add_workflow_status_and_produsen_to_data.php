<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {

            // produsen_id nullable
            $table->integer('produsen_id')
                  ->nullable()
                  ->after('status');

            // workflow status
            $table->string('workflow_status', 30)
                  ->default('draft')
                  ->after('produsen_id');

            // reviewer note
            $table->text('reviewer_note')
                  ->nullable()
                  ->after('workflow_status');

            // reviewed by
            $table->integer('reviewed_by')
                  ->nullable()
                  ->after('reviewer_note');

            // reviewed at
            $table->timestamp('reviewed_at')
                  ->nullable()
                  ->after('reviewed_by');

            // index
            $table->index('workflow_status');
        });

        // foreign key dipisah
        Schema::table('data', function (Blueprint $table) {

            $table->foreign('produsen_id')
                ->references('produsen_id')
                ->on('produsen_data')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('reviewed_by')
                ->references('user_id')
                ->on('user')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('data', function (Blueprint $table) {

            $table->dropForeign(['produsen_id']);
            $table->dropForeign(['reviewed_by']);

            $table->dropIndex(['workflow_status']);

            $table->dropColumn([
                'produsen_id',
                'workflow_status',
                'reviewer_note',
                'reviewed_by',
                'reviewed_at'
            ]);
        });
    }
};