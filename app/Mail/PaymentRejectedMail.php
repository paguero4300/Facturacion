<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $rejectionReason = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Necesitamos verificar tu pago - Pedido ' . $this->invoice->full_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-rejected',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}