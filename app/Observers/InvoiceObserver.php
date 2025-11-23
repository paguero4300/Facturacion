<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;

class InvoiceObserver
{
    public function saved(Invoice $invoice)
    {
        $this->calculateTotals($invoice);
    }

    public function updated(Invoice $invoice)
    {
        $this->calculateTotals($invoice);

        // Manejo de Stock al cambiar estado
        if ($invoice->isDirty('status')) {
            $newStatus = $invoice->status;
            $oldStatus = $invoice->getOriginal('status');

            // Si se cancela el pedido, reponer stock
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($invoice->details as $detail) {
                    if ($product = $detail->product) {
                        $product->increaseStock($detail->quantity);
                    }
                }
            }

            // Si se reactiva un pedido cancelado (vuelve a draft o paid), descontar stock nuevamente
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                foreach ($invoice->details as $detail) {
                    if ($product = $detail->product) {
                        $product->decreaseStock($detail->quantity);
                    }
                }
            }
        }

        // Manejo de Stock al rechazar pago (si no estaba ya cancelado)
        if ($invoice->isDirty('payment_validation_status')) {
            $newValidation = $invoice->payment_validation_status;
            
            // Si se rechaza el pago, reponer stock (asumiendo que no se canceló el pedido aún)
            // Nota: Si el flujo implica cancelar el pedido al rechazar pago, la lógica de arriba ya lo cubre.
            // Pero si el pedido queda en 'draft' pero con pago rechazado, deberíamos liberar el stock?
            // Por ahora, asumimos que rechazar pago LIBERA el stock para que otros compren.
            if ($newValidation === \App\Enums\PaymentValidationStatus::PAYMENT_REJECTED) {
                foreach ($invoice->details as $detail) {
                    if ($product = $detail->product) {
                        $product->increaseStock($detail->quantity);
                    }
                }
            }
        }
    }

    protected function calculateTotals(Invoice $invoice)
    {
        $details = $invoice->details;
        
        $subtotal = 0;
        $igvAmount = 0;
        $totalAmount = 0;

        foreach ($details as $detail) {
            $subtotal += $detail->net_amount;
            $igvAmount += $detail->igv_amount;
            $totalAmount += $detail->line_total;
        }

        // Avoid event recursion; update totals quietly
        $invoice->forceFill([
            'subtotal' => $subtotal,
            'igv_amount' => $igvAmount,
            'total_amount' => $totalAmount,
            'pending_amount' => $totalAmount - $invoice->paid_amount,
        ])->saveQuietly();

        // Auto-generate installments for credit invoices once totals are available
        try {
            if ($invoice->payment_condition === 'credit' && $totalAmount > 0 && $invoice->details()->exists()) {
                $alreadyGenerated = (bool) data_get($invoice->additional_data ?? [], 'installments_generated', false);
                if (! $alreadyGenerated && $invoice->paymentInstallments()->count() === 0) {
                    $this->generateInstallments($invoice);
                }
            }
        } catch (\Throwable $e) {
            // Swallow to avoid breaking save; consider logging if needed
        }
    }

    protected function generateInstallments(Invoice $invoice): void
    {
        $cfg = $invoice->additional_data ?? [];
        $count = max(1, (int) ($cfg['installments_count'] ?? 1));
        $interval = max(1, (int) ($cfg['installment_interval_days'] ?? 30));
        $firstDue = isset($cfg['first_due_date']) ? \Carbon\Carbon::parse($cfg['first_due_date']) : (\Carbon\Carbon::parse($invoice->issue_date)->addDays($interval));

        $total = (float) $invoice->total_amount;
        if ($total <= 0) return;

        $base = round($total / $count, 2);
        $amounts = array_fill(0, $count, $base);
        $sum = array_sum($amounts);
        $diff = round($total - $sum, 2);
        if ($diff !== 0.0) {
            // Adjust last installment with the rounding difference
            $amounts[$count - 1] = round($amounts[$count - 1] + $diff, 2);
        }

        for ($i = 0; $i < $count; $i++) {
            $due = (clone $firstDue)->addDays($interval * $i);
            $invoice->paymentInstallments()->create([
                'installment_number' => $i + 1,
                'amount' => $amounts[$i],
                'due_date' => $due->toDateString(),
                'paid_amount' => 0,
                'pending_amount' => $amounts[$i],
                'status' => 'pending',
            ]);
        }

        $data = $invoice->additional_data ?? [];
        $data['installments_generated'] = true;
        $invoice->additional_data = $data;
        $invoice->saveQuietly();
    }
}
