<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InventoryReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InventoryReport');
    }

    public function view(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('View:InventoryReport');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InventoryReport');
    }

    public function update(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('Update:InventoryReport');
    }

    public function delete(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('Delete:InventoryReport');
    }

    public function restore(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('Restore:InventoryReport');
    }

    public function forceDelete(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('ForceDelete:InventoryReport');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InventoryReport');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InventoryReport');
    }

    public function replicate(AuthUser $authUser, InventoryReport $inventoryReport): bool
    {
        return $authUser->can('Replicate:InventoryReport');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InventoryReport');
    }

}