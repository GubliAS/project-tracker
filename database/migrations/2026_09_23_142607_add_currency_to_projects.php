<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->after('spent');
        });

        if (Schema::hasColumn('workspaces', 'currency')) {
            $workspaces = DB::table('workspaces')->get(['id', 'currency']);

            foreach ($workspaces as $workspace) {
                DB::table('projects')
                    ->where('workspace_id', $workspace->id)
                    ->whereNull('currency')
                    ->update(['currency' => $workspace->currency ?: 'USD']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
