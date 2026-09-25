<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

class UniqueConstraintMapper
{
    /**
     * @return array<string, string>
     */
    public static function messages(UniqueConstraintViolationException $exception): array
    {
        $sql = $exception->getMessage();

        if (self::mentions($sql, ['users_name', 'users.name', 'users (name)'])) {
            return ['name' => 'A user with this name already exists.'];
        }

        if (self::mentions($sql, ['users_email', 'users.email', 'users (email)'])) {
            return ['email' => 'A user with this email already exists.'];
        }

        if (self::mentions($sql, ['invitations', 'workspace_user'])) {
            return ['email' => 'That person is already invited or belongs to this workspace.'];
        }

        if (self::mentions($sql, ['projects_name', 'projects.name'])) {
            return ['name' => 'A project with this name already exists.'];
        }

        if (self::mentions($sql, ['workspaces_slug', 'workspaces.slug', 'workspaces (slug)'])) {
            return ['name' => 'A workspace with this name already exists.'];
        }

        return ['name' => 'This value is already in use.'];
    }

    /**
     * @return array<string, string>
     */
    public static function queryMessages(QueryException $exception): array
    {
        if ($exception instanceof UniqueConstraintViolationException) {
            return self::messages($exception);
        }

        $sql = $exception->getMessage();

        if (self::mentions($sql, ['project_id'])) {
            return ['project_id' => 'The selected project is invalid.'];
        }

        if (self::mentions($sql, ['sprint_id'])) {
            return ['sprint_id' => 'The selected sprint is invalid.'];
        }

        if (self::mentions($sql, ['resource_id'])) {
            return ['resource_id' => 'The selected resource is invalid.'];
        }

        if (self::mentions($sql, ['task_id'])) {
            return ['task_id' => 'The selected task is invalid.'];
        }

        if (self::mentions($sql, ['user_id'])) {
            return ['user_id' => 'The selected user is invalid.'];
        }

        if (self::mentions($sql, ['workspace_id'])) {
            return ['name' => 'The selected workspace is invalid.'];
        }

        return ['name' => 'This value could not be saved. Check the form and try again.'];
    }

    public static function isIntegrityViolation(QueryException $exception): bool
    {
        $code = (string) $exception->getCode();
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = $exception->getMessage();

        return $code === '23000'
            || $sqlState === '23000'
            || str_contains($message, 'FOREIGN KEY')
            || str_contains($message, 'foreign key')
            || str_contains($message, 'NOT NULL constraint')
            || str_contains($message, 'integrity constraint');
    }

    /**
     * @param  list<string>  $needles
     */
    private static function mentions(string $sql, array $needles): bool
    {
        $haystack = strtolower($sql);

        foreach ($needles as $needle) {
            if (str_contains($haystack, strtolower($needle))) {
                return true;
            }
        }

        return false;
    }
}
