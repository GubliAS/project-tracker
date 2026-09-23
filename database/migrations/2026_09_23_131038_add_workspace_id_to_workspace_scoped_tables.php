<?php

use App\Enums\WorkspaceRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private array $workspaceScopedTables = [
        'projects',
        'resources',
        'workflows',
        'changelogs',
        'definition_items',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->workspaceScopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('workspace_id')->nullable()->after('id')->constrained()->nullOnDelete();
            });
        }

        $now = now();
        $workspaceId = DB::table('workspaces')->insertGetId([
            'name' => 'Kedebah',
            'slug' => 'kedebah',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($this->workspaceScopedTables as $tableName) {
            DB::table($tableName)->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }

        $testUser = DB::table('users')->where('email', 'test@example.com')->first();

        if ($testUser) {
            DB::table('users')->where('id', $testUser->id)->update(['is_platform_admin' => true]);

            $alreadyMember = DB::table('workspace_user')
                ->where('workspace_id', $workspaceId)
                ->where('user_id', $testUser->id)
                ->exists();

            if (! $alreadyMember) {
                DB::table('workspace_user')->insert([
                    'workspace_id' => $workspaceId,
                    'user_id' => $testUser->id,
                    'role' => WorkspaceRole::WorkspaceAdmin->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->workspaceScopedTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('workspace_id');
            });
        }

        DB::table('workspace_user')->where('role', WorkspaceRole::WorkspaceAdmin->value)->delete();
        DB::table('workspaces')->where('slug', 'kedebah')->delete();
    }
};
