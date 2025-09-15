<?php

namespace App\Observers;

use App\Models\InvoiceDetail;

class InvoiceDetailObserver
{
    public function saved(InvoiceDetail $detail): void
    {
        if ($invoice = $detail->invoice) {
            // Recalculate parent invoice totals quietly to avoid recursion
            $invoice->calculateTotals();
        }
    }

    public function deleted(InvoiceDetail $detail): void
    {
        if ($invoice = $detail->invoice) {
            $invoice->refresh();
            $invoice->calculateTotals();
        }
    }
}

