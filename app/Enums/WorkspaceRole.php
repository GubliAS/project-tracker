<?php

namespace App\Enums;

enum WorkspaceRole: string
{
    case WorkspaceAdmin = 'workspace_admin';
    case ProjectManager = 'project_manager';
    case Member = 'member';
    case Viewer = 'viewer';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::WorkspaceAdmin => 'Workspace admin',
            self::ProjectManager => 'Project manager',
            self::Member => 'Member',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageMembers(): bool
    {
        return $this === self::WorkspaceAdmin;
    }

    public function canManageWorkspace(): bool
    {
        return $this === self::WorkspaceAdmin;
    }

    public function canWriteProjects(): bool
    {
        return in_array($this, [self::WorkspaceAdmin, self::ProjectManager], true);
    }

    public function canWriteOps(): bool
    {
        return in_array($this, [self::WorkspaceAdmin, self::ProjectManager], true);
    }

    public function canWriteMemberContent(): bool
    {
        return in_array($this, [self::WorkspaceAdmin, self::ProjectManager, self::Member], true);
    }

    public function isReadOnly(): bool
    {
        return $this === self::Viewer;
    }
}
