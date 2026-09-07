<?php

declare(strict_types=1);

namespace Nafiswatsiq\Subbase\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Nafiswatsiq\Subbase\Models\Discount;
use Nafiswatsiq\Subbase\Support\SubbasePermission;
use Illuminate\Auth\Access\HandlesAuthorization;

class DiscountPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'viewAny', Discount::class);
    }

    public function view(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'view', Discount::class);
    }

    public function create(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'create', Discount::class);
    }

    public function update(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'update', Discount::class);
    }

    public function delete(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'delete', Discount::class);
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'deleteAny', Discount::class);
    }

    public function restore(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'restore', Discount::class);
    }

    public function forceDelete(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'forceDelete', Discount::class);
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'forceDeleteAny', Discount::class);
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'restoreAny', Discount::class);
    }

    public function replicate(AuthUser $authUser, Discount $discount): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'replicate', Discount::class);
    }

    public function reorder(AuthUser $authUser): bool
    {
        return SubbasePermission::allows(config('subbase.permissions.discount'), 'reorder', Discount::class);
    }
}