<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Nafiswatsiq\Subbase\Models\Plan;
use Nafiswatsiq\Subbase\Support\SubbasePermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PlanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'viewAny', Plan::class);
    }

    public function view(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'view', Plan::class);
    }

    public function create(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'create', Plan::class);
    }

    public function update(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'update', Plan::class);
    }

    public function delete(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'delete', Plan::class);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'deleteAny', Plan::class);
    }

    public function restore(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'restore', Plan::class);
    }

    public function forceDelete(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'forceDelete', Plan::class);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'forceDeleteAny', Plan::class);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'restoreAny', Plan::class);
    }

    public function replicate(AuthUser $authUser, Plan $plan): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'replicate', Plan::class);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.plan'), 'reorder', Plan::class);
    }
}