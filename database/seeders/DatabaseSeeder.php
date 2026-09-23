<?php

namespace Database\Seeders;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_platform_admin' => true,
            ],
        );

        $workspace = Workspace::query()->firstOrCreate(
            ['slug' => 'kedebah'],
            ['name' => 'Kedebah'],
        );

        $workspace->users()->syncWithoutDetaching([
            $user->id => ['role' => WorkspaceRole::WorkspaceAdmin->value],
        ]);
    }
}
