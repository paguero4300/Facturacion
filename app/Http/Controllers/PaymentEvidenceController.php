<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentEvidenceController extends Controller
{
    public function show(Request $request, $invoiceId): StreamedResponse
    {
        $invoice = Invoice::findOrFail($invoiceId);

        // Verificar que existe evidencia de pago
        if (!$invoice->payment_evidence_path) {
            abort(404, 'Comprobante de pago no encontrado');
        }

        $disk = Storage::disk('payment_evidences');

        if (!$disk->exists($invoice->payment_evidence_path)) {
            abort(404, 'Archivo no encontrado');
        }

        $extension = pathinfo($invoice->payment_evidence_path, PATHINFO_EXTENSION);
        $mimeType = match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };

        return $disk->response($invoice->payment_evidence_path, null, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
    }
}
