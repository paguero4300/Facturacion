<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliveryScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Entrega Programada - Pedido {$this->invoice->full_number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.delivery.scheduled',
            with: [
                'invoice' => $this->invoice,
                'customerName' => $this->invoice->client_business_name,
                'orderNumber' => $this->invoice->full_number,
                'deliveryDate' => $this->invoice->delivery_date,
                'deliveryTimeSlot' => $this->invoice->delivery_time_slot,
                'deliveryAddress' => $this->invoice->client_address,
                'deliveryNotes' => $this->invoice->delivery_notes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
