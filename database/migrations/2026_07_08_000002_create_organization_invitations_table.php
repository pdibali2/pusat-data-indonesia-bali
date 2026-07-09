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
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->integer('organization_invitation_id')->autoIncrement();
            $table->integer('organization_id');
            $table->foreign('organization_id')
                ->references('organization_id')
                ->on('organizations')
                ->onDelete('cascade');
            $table->string('email', 100);
            $table->string('token', 64)->unique();
            $table->integer('user_id')->nullable();
            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->nullOnDelete();
            $table->enum('status', ['invited', 'accepted', 'expired'])->default('invited');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_invitations');
    }
};
