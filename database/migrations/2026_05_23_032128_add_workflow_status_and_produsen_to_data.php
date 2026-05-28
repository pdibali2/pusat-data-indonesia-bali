<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
 public function up(): void
    {
        Schema::table('data', function (Blueprint $table) {
            $table->integer('produsen_id')->nullable();
            // Workflow status: draft | warning | under_review | approved
            // approved_with_note | rejected | revised
            $table->string('workflow_status', 30)
            ->default('draft')
            ->after('status');
            // Catatan dari reviewer (untuk approved_with_note)
            $table->text('reviewer_note')->nullable()->after('workflow_status');
            // Siapa yang terakhir review
            $table->integer('reviewed_by')->nullable()->after('reviewer_note');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            
            $table->foreign('produsen_id')
            ->references('produsen_id')
            ->on('produsen_data')
            ->onDelete('set null')
            ->onUpdate('restrict');
            
            $table->foreign('reviewed_by')
            ->references('user_id')
            ->on('user')
            ->onDelete('set null')
            ->onUpdate('cascade');
            $table->index('workflow_status');
            });
        }
        public function down(): void
        {
            Schema::table('data', function (Blueprint $table) {
            $table->dropForeign(['produsen_id']);
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['workflow_status']);
            $table->dropColumn(['produsen_id', 'workflow_status', 'reviewer_note', 'reviewed_by', 'reviewed_at']);
        });
    }
};