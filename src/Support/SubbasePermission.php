<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Support;

use Illuminate\Support\Facades\Auth;

final class SubbasePermission
{
    public static function allows(?string $permission): bool
    {
        $permission = trim((string) $permission);

        if ($permission === '') {
            return true;
        }

        if (! class_exists('Spatie\\Permission\\PermissionRegistrar')) {
            return true;
        }

        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
