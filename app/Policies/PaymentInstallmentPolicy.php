<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaymentInstallment;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentInstallmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentInstallment');
    }

    public function view(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('View:PaymentInstallment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentInstallment');
    }

    public function update(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('Update:PaymentInstallment');
    }

    public function delete(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('Delete:PaymentInstallment');
    }

    public function restore(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('Restore:PaymentInstallment');
    }

    public function forceDelete(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('ForceDelete:PaymentInstallment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentInstallment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentInstallment');
    }

    public function replicate(AuthUser $authUser, PaymentInstallment $paymentInstallment): bool
    {
        return $authUser->can('Replicate:PaymentInstallment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentInstallment');
    }

}