<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\InvoiceDetail;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoiceDetailPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:InvoiceDetail');
    }

    public function view(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('View:InvoiceDetail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:InvoiceDetail');
    }

    public function update(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('Update:InvoiceDetail');
    }

    public function delete(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('Delete:InvoiceDetail');
    }

    public function restore(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('Restore:InvoiceDetail');
    }

    public function forceDelete(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('ForceDelete:InvoiceDetail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:InvoiceDetail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:InvoiceDetail');
    }

    public function replicate(AuthUser $authUser, InvoiceDetail $invoiceDetail): bool
    {
        return $authUser->can('Replicate:InvoiceDetail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:InvoiceDetail');
    }

}