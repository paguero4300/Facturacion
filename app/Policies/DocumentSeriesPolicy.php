<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DocumentSeries;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentSeriesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DocumentSeries');
    }

    public function view(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('View:DocumentSeries');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DocumentSeries');
    }

    public function update(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('Update:DocumentSeries');
    }

    public function delete(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('Delete:DocumentSeries');
    }

    public function restore(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('Restore:DocumentSeries');
    }

    public function forceDelete(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('ForceDelete:DocumentSeries');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DocumentSeries');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DocumentSeries');
    }

    public function replicate(AuthUser $authUser, DocumentSeries $documentSeries): bool
    {
        return $authUser->can('Replicate:DocumentSeries');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DocumentSeries');
    }

}