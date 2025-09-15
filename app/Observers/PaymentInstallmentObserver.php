<?php

namespace App\Observers;

use App\Models\PaymentInstallment;

class PaymentInstallmentObserver
{
    public function saving(PaymentInstallment $installment): void
    {
        // Keep pending_amount consistent with amount - paid_amount
        $amount = (float) ($installment->amount ?? 0);
        $paid = (float) ($installment->paid_amount ?? 0);
        $installment->pending_amount = max(0, $amount - $paid);

        // Derive status from amounts and due date
        if ($installment->pending_amount <= 0) {
            $installment->status = 'paid';
        } elseif ($paid > 0) {
            $installment->status = 'partial_paid';
        } else {
            $installment->status = now()->gt($installment->due_date ?? now()) ? 'overdue' : 'pending';
        }
    }

    public function saved(PaymentInstallment $installment): void
    {
        $this->syncInvoiceTotals($installment);
    }

    public function deleted(PaymentInstallment $installment): void
    {
        $this->syncInvoiceTotals($installment);
    }

    protected function syncInvoiceTotals(PaymentInstallment $installment): void
    {
        $invoice = $installment->invoice;
        if (! $invoice) return;

        $paid = (float) $invoice->paymentInstallments()->sum('paid_amount');
        $invoice->paid_amount = $paid;
        $invoice->pending_amount = max(0, (float) $invoice->total_amount - $paid);

        if ($invoice->pending_amount <= 0) {
            $invoice->status = 'paid';
        } elseif ($paid > 0) {
            $invoice->status = 'partial_paid';
        } else {
            // Keep current unless cancelled/voided etc.
            $invoice->status = $invoice->status === 'cancelled' ? 'cancelled' : 'issued';
        }

        $invoice->saveQuietly();
    }
}

