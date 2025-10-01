<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\DeliveryNotificationService;
use App\Enums\DeliveryStatus;

class InvoiceDeliveryObserver
{
    private DeliveryNotificationService $notificationService;

    public function __construct(DeliveryNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Send notification when a new invoice with delivery is created
        if ($invoice->hasDeliveryScheduled()) {
            $this->notificationService->sendDeliveryScheduledNotification($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Check if delivery status has changed
        if ($invoice->isDirty('delivery_status') && $invoice->delivery_status) {
            $previousStatus = DeliveryStatus::from($invoice->getOriginal('delivery_status'));
            $this->notificationService->sendDeliveryStatusChangeNotification($invoice, $previousStatus);
        }
        
        // Check if delivery was just scheduled (from null to scheduled)
        if ($invoice->isDirty(['delivery_date', 'delivery_time_slot']) && $invoice->hasDeliveryScheduled()) {
            $wasScheduled = $invoice->getOriginal('delivery_date') && $invoice->getOriginal('delivery_time_slot');
            if (!$wasScheduled) {
                $this->notificationService->sendDeliveryScheduledNotification($invoice);
            }
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Could add logic here for cancelled delivery notifications
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        //
    }
}
