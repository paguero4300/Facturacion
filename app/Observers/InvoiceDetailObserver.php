<?php

namespace App\Observers;

use App\Models\InvoiceDetail;

class InvoiceDetailObserver
{
    public function created(InvoiceDetail $detail): void
    {
        if ($product = $detail->product) {
            $product->decreaseStock($detail->quantity);
        }

        if ($invoice = $detail->invoice) {
            $invoice->calculateTotals();
        }
    }

    public function saved(InvoiceDetail $detail): void
    {
        if ($invoice = $detail->invoice) {
            // Recalculate parent invoice totals quietly to avoid recursion
            $invoice->calculateTotals();
        }
    }

    public function deleted(InvoiceDetail $detail): void
    {
        // Solo reponer stock si la factura NO estaba cancelada (porque si estaba cancelada, ya se repuso)
        if ($invoice = $detail->invoice) {
            if ($invoice->status !== 'cancelled' && $invoice->payment_validation_status !== \App\Enums\PaymentValidationStatus::PAYMENT_REJECTED) {
                if ($product = $detail->product) {
                    $product->increaseStock($detail->quantity);
                }
            }
            
            $invoice->refresh();
            $invoice->calculateTotals();
        }
    }
}

