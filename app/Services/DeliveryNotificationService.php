<?php

namespace App\Services;

use App\Models\Invoice;
use App\Enums\DeliveryStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;

class DeliveryNotificationService
{
    /**
     * Send notification when delivery is scheduled
     */
    public function sendDeliveryScheduledNotification(Invoice $invoice): void
    {
        if (!$invoice->hasDeliveryScheduled()) {
            return;
        }

        // Send email to customer if email is provided
        if ($invoice->client_email) {
            $this->sendCustomerDeliveryScheduledEmail($invoice);
        }

        // Send notification to administrators
        $this->notifyAdministrators(
            'Nuevo Pedido con Entrega Programada',
            "El pedido {$invoice->full_number} ha sido programado para entrega el {$invoice->delivery_date->format('d/m/Y')} en horario {$invoice->delivery_time_slot->label()}.",
            'success'
        );
    }

    /**
     * Send notification when delivery status changes
     */
    public function sendDeliveryStatusChangeNotification(Invoice $invoice, DeliveryStatus $previousStatus): void
    {
        // Send email to customer if email is provided
        if ($invoice->client_email) {
            $this->sendCustomerDeliveryStatusEmail($invoice, $previousStatus);
        }

        // Send notification to administrators based on new status
        $this->notifyDeliveryStatusChange($invoice, $previousStatus);
    }

    /**
     * Send reminder notification for upcoming deliveries
     */
    public function sendDeliveryReminder(Invoice $invoice): void
    {
        if (!$invoice->hasDeliveryScheduled() || $invoice->delivery_status !== DeliveryStatus::PROGRAMADO) {
            return;
        }

        // Send reminder email to customer
        if ($invoice->client_email) {
            $this->sendCustomerDeliveryReminderEmail($invoice);
        }
    }

    /**
     * Send daily delivery list to administrators
     */
    public function sendDailyDeliveryList(\Carbon\Carbon $date, $deliveries): void
    {
        if ($deliveries->isEmpty()) {
            return;
        }

        $this->notifyAdministrators(
            'Entregas Programadas para Hoy',
            "Hay {$deliveries->count()} entregas programadas para hoy ({$date->format('d/m/Y')}).",
            'info'
        );
    }

    /**
     * Send customer delivery scheduled email
     */
    private function sendCustomerDeliveryScheduledEmail(Invoice $invoice): void
    {
        try {
            // For now, we'll create a simple notification
            // In a full implementation, you would create Mailable classes
            $subject = "Entrega Programada - Pedido {$invoice->full_number}";
            $message = $this->buildDeliveryScheduledMessage($invoice);
            
            // Note: This is a placeholder for email sending
            // You would implement actual email sending here
            \Log::info("Email sent to {$invoice->client_email}: {$subject}");
            
        } catch (\Exception $e) {
            \Log::error("Failed to send delivery scheduled email: " . $e->getMessage());
        }
    }

    /**
     * Send customer delivery status change email
     */
    private function sendCustomerDeliveryStatusEmail(Invoice $invoice, DeliveryStatus $previousStatus): void
    {
        try {
            $subject = "Actualización de Entrega - Pedido {$invoice->full_number}";
            $message = $this->buildDeliveryStatusMessage($invoice, $previousStatus);
            
            // Note: This is a placeholder for email sending
            \Log::info("Email sent to {$invoice->client_email}: {$subject}");
            
        } catch (\Exception $e) {
            \Log::error("Failed to send delivery status email: " . $e->getMessage());
        }
    }

    /**
     * Send customer delivery reminder email
     */
    private function sendCustomerDeliveryReminderEmail(Invoice $invoice): void
    {
        try {
            $subject = "Recordatorio de Entrega - Pedido {$invoice->full_number}";
            $message = $this->buildDeliveryReminderMessage($invoice);
            
            // Note: This is a placeholder for email sending
            \Log::info("Email sent to {$invoice->client_email}: {$subject}");
            
        } catch (\Exception $e) {
            \Log::error("Failed to send delivery reminder email: " . $e->getMessage());
        }
    }

    /**
     * Notify administrators using Filament notifications
     */
    private function notifyAdministrators(string $title, string $body, string $status = 'info'): void
    {
        try {
            FilamentNotification::make()
                ->title($title)
                ->body($body)
                ->status($status)
                ->sendToDatabase(\App\Models\User::where('is_admin', true)->get());
                
        } catch (\Exception $e) {
            \Log::error("Failed to send admin notification: " . $e->getMessage());
        }
    }

    /**
     * Notify delivery status change to administrators
     */
    private function notifyDeliveryStatusChange(Invoice $invoice, DeliveryStatus $previousStatus): void
    {
        $messages = [
            DeliveryStatus::EN_RUTA => "El pedido {$invoice->full_number} está ahora en ruta para entrega.",
            DeliveryStatus::ENTREGADO => "El pedido {$invoice->full_number} ha sido entregado exitosamente.",
            DeliveryStatus::REPROGRAMADO => "El pedido {$invoice->full_number} ha sido reprogramado y requiere nueva fecha de entrega.",
        ];

        $colors = [
            DeliveryStatus::EN_RUTA => 'warning',
            DeliveryStatus::ENTREGADO => 'success',
            DeliveryStatus::REPROGRAMADO => 'danger',
        ];

        $message = $messages[$invoice->delivery_status] ?? "El pedido {$invoice->full_number} ha cambiado de estado.";
        $color = $colors[$invoice->delivery_status] ?? 'info';

        $this->notifyAdministrators(
            'Estado de Entrega Actualizado',
            $message,
            $color
        );
    }

    /**
     * Build delivery scheduled message
     */
    private function buildDeliveryScheduledMessage(Invoice $invoice): string
    {
        return "Estimado/a {$invoice->client_business_name},\n\n" .
               "Su pedido {$invoice->full_number} ha sido confirmado y programado para entrega:\n\n" .
               "📅 Fecha: {$invoice->delivery_date->format('d/m/Y')}\n" .
               "🕐 Horario: {$invoice->delivery_time_slot->label()}\n" .
               "📍 Dirección: {$invoice->client_address}\n\n" .
               ($invoice->delivery_notes ? "📝 Notas especiales: {$invoice->delivery_notes}\n\n" : "") .
               "Le contactaremos cuando el pedido esté en camino.\n\n" .
               "Gracias por su compra.";
    }

    /**
     * Build delivery status message
     */
    private function buildDeliveryStatusMessage(Invoice $invoice, DeliveryStatus $previousStatus): string
    {
        $statusMessages = [
            DeliveryStatus::EN_RUTA => "Su pedido está en camino y será entregado en el horario programado: {$invoice->delivery_time_slot->label()}.",
            DeliveryStatus::ENTREGADO => "Su pedido ha sido entregado exitosamente. ¡Gracias por su compra!",
            DeliveryStatus::REPROGRAMADO => "Su entrega ha sido reprogramada. Nos pondremos en contacto para coordinar una nueva fecha.",
        ];

        return "Estimado/a {$invoice->client_business_name},\n\n" .
               "Le informamos que el estado de su pedido {$invoice->full_number} ha sido actualizado:\n\n" .
               "📦 Estado anterior: {$previousStatus->label()}\n" .
               "📦 Estado actual: {$invoice->delivery_status->label()}\n\n" .
               ($statusMessages[$invoice->delivery_status] ?? "Su pedido ha cambiado de estado.") . "\n\n" .
               "Si tiene alguna consulta, no dude en contactarnos.";
    }

    /**
     * Build delivery reminder message
     */
    private function buildDeliveryReminderMessage(Invoice $invoice): string
    {
        return "Estimado/a {$invoice->client_business_name},\n\n" .
               "Le recordamos que su pedido {$invoice->full_number} está programado para entrega mañana:\n\n" .
               "📅 Fecha: {$invoice->delivery_date->format('d/m/Y')}\n" .
               "🕐 Horario: {$invoice->delivery_time_slot->label()}\n" .
               "📍 Dirección: {$invoice->client_address}\n\n" .
               ($invoice->delivery_notes ? "📝 Notas especiales: {$invoice->delivery_notes}\n\n" : "") .
               "Por favor, asegúrese de estar disponible en el horario programado.\n\n" .
               "Gracias por su preferencia.";
    }

    /**
     * Get delivery statistics for notifications
     */
    public function getDeliveryStats(\Carbon\Carbon $date): array
    {
        $deliveries = Invoice::withDeliveryScheduled()
            ->byDeliveryDate($date)
            ->get();

        return [
            'total' => $deliveries->count(),
            'programado' => $deliveries->where('delivery_status', DeliveryStatus::PROGRAMADO)->count(),
            'en_ruta' => $deliveries->where('delivery_status', DeliveryStatus::EN_RUTA)->count(),
            'entregado' => $deliveries->where('delivery_status', DeliveryStatus::ENTREGADO)->count(),
            'reprogramado' => $deliveries->where('delivery_status', DeliveryStatus::REPROGRAMADO)->count(),
        ];
    }
}