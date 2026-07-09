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
        Schema::create('organizations', function (Blueprint $table) {
            $table->integer('organization_id')->autoIncrement();
            $table->string('name');

            $table->integer('owner_id');
            $table->foreign('owner_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('restrict');

            $table->timestamps();
        });

        Schema::create('organization_members', function (Blueprint $table) {
            $table->integer('organization_member_id')->autoIncrement();

            $table->integer('organization_id');
            $table->foreign('organization_id')
                ->references('organization_id')
                ->on('organizations')
                ->onDelete('cascade');

            $table->integer('user_id');
            $table->foreign('user_id')
                ->references('user_id')
                ->on('user')
                ->onDelete('restrict');

            $table->enum('role', ['owner', 'member'])->default('member');
            $table->enum('status', ['invited', 'active', 'removed'])->default('invited');
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
        });

        Schema::table('layanan', function (Blueprint $table) {
            $table->enum('audience_type', ['personal', 'organization'])->default('personal')->after('durasi_type');
            $table->integer('organization_id')->nullable()->after('audience_type');
            $table->foreign('organization_id')
                ->references('organization_id')
                ->on('organizations')
                ->nullOnDelete();
            $table->integer('max_seats')->nullable()->default(1)->after('organization_id');
            $table->integer('max_concurrent_sessions')->default(1)->after('max_seats');
            $table->enum('category', ['personal', 'organisasi'])->nullable()->after('audience_type');
            $table->integer('max_templates')->nullable()->default(10)->after('max_concurrent_sessions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn(['audience_type', 'organization_id', 'max_seats', 'max_concurrent_sessions']);
        });

        Schema::dropIfExists('organization_members');
        Schema::dropIfExists('organizations');
    }
};
