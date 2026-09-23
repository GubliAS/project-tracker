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
        Schema::table('resources', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->unsignedTinyInteger('availability_percent')->nullable()->after('availability_status');
        });

        Schema::table('risks', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('owner')->nullable()->after('category');
        });

        Schema::table('changelogs', function (Blueprint $table) {
            $table->string('requestor')->nullable()->after('title');
            $table->string('approval_status')->nullable()->after('type');
            $table->string('impact')->nullable()->after('approval_status');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });

        Schema::table('lesson_learneds', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->string('impact_sentiment')->nullable()->after('impact_level');
        });

        Schema::table('quality_checks', function (Blueprint $table) {
            $table->string('priority')->nullable()->after('status');
            $table->timestamp('last_run_at')->nullable()->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['email', 'availability_percent']);
        });

        Schema::table('risks', function (Blueprint $table) {
            $table->dropColumn(['category', 'owner']);
        });

        Schema::table('changelogs', function (Blueprint $table) {
            $table->dropColumn(['requestor', 'approval_status', 'impact']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('lesson_learneds', function (Blueprint $table) {
            $table->dropColumn(['description', 'impact_sentiment']);
        });

        Schema::table('quality_checks', function (Blueprint $table) {
            $table->dropColumn(['priority', 'last_run_at']);
        });
    }
};
