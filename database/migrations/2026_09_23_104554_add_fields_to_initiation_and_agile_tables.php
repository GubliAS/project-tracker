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
        Schema::table('kickoffs', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->date('scheduled_on')->nullable();
            $table->unsignedInteger('attendees')->default(0);
            $table->string('status')->default('scheduled');
            $table->json('objectives')->nullable();
        });

        Schema::table('stakeholders', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('department')->nullable();
            $table->string('influence')->default('medium');
            $table->string('interest')->default('medium');
        });

        Schema::table('sprints', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('goal')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('planned');
            $table->unsignedInteger('story_points')->default(0);
            $table->unsignedInteger('completed_points')->default(0);
        });

        Schema::table('backlog_items', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('story');
            $table->string('priority')->default('medium');
            $table->unsignedInteger('points')->default(0);
            $table->string('status')->default('backlog');
        });

        Schema::table('workflows', function (Blueprint $table) {
            $table->string('name');
            $table->json('stages')->nullable();
        });

        Schema::table('definition_items', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->string('text');
            $table->boolean('is_checked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kickoffs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['scheduled_on', 'attendees', 'status', 'objectives']);
        });

        Schema::table('stakeholders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['name', 'role', 'department', 'influence', 'interest']);
        });

        Schema::table('sprints', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['name', 'goal', 'start_date', 'end_date', 'status', 'story_points', 'completed_points']);
        });

        Schema::table('backlog_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropConstrainedForeignId('sprint_id');
            $table->dropColumn(['title', 'type', 'priority', 'points', 'status']);
        });

        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['name', 'stages']);
        });

        Schema::table('definition_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn(['kind', 'text', 'is_checked']);
        });
    }
};
