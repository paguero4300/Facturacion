<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\WebConfiguration;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebConfigurationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:WebConfiguration');
    }

    public function view(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('View:WebConfiguration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:WebConfiguration');
    }

    public function update(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('Update:WebConfiguration');
    }

    public function delete(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('Delete:WebConfiguration');
    }

    public function restore(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('Restore:WebConfiguration');
    }

    public function forceDelete(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('ForceDelete:WebConfiguration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:WebConfiguration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:WebConfiguration');
    }

    public function replicate(AuthUser $authUser, WebConfiguration $webConfiguration): bool
    {
        return $authUser->can('Replicate:WebConfiguration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:WebConfiguration');
    }

}