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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('team')->nullable()->after('status');
            $table->string('client')->nullable()->after('team');
            $table->string('priority')->default('medium')->after('client');
            $table->string('project_type')->nullable()->after('priority');
            $table->date('start_date')->nullable()->after('project_type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->decimal('budget', 12, 2)->nullable()->after('end_date');
            $table->decimal('spent', 12, 2)->default(0)->after('budget');
            $table->json('settings')->nullable()->after('spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'team',
                'client',
                'priority',
                'project_type',
                'start_date',
                'end_date',
                'budget',
                'spent',
                'settings',
            ]);
        });
    }
};
