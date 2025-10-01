<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentEvidenceController extends Controller
{
    /**
     * Serve payment evidence files securely
     */
    public function show(Request $request, Invoice $invoice): BinaryFileResponse|Response
    {
        // Verificar que el usuario tenga permisos para ver este comprobante
        if (!$this->canViewPaymentEvidence($invoice)) {
            abort(403, 'No tienes permisos para acceder a este archivo.');
        }

        // Verificar que el archivo existe
        if (!$invoice->hasPaymentEvidence()) {
            abort(404, 'Archivo de comprobante no encontrado.');
        }

        try {
            $filePath = $invoice->payment_evidence_path;
            
            // Obtener el archivo del storage privado
            if (!Storage::disk('payment_evidences')->exists($filePath)) {
                abort(404, 'Archivo no encontrado en el almacenamiento.');
            }

            $fullPath = Storage::disk('payment_evidences')->path($filePath);
            $mimeType = Storage::disk('payment_evidences')->mimeType($filePath);
            
            // Obtener el nombre original del archivo
            $originalName = pathinfo($filePath, PATHINFO_BASENAME);
            
            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $originalName . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error serving payment evidence file', [
                'invoice_id' => $invoice->id,
                'file_path' => $invoice->payment_evidence_path,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Error al acceder al archivo.');
        }
    }

    /**
     * Download payment evidence file
     */
    public function download(Request $request, Invoice $invoice): BinaryFileResponse|Response
    {
        // Verificar permisos
        if (!$this->canViewPaymentEvidence($invoice)) {
            abort(403, 'No tienes permisos para descargar este archivo.');
        }

        // Verificar que el archivo existe
        if (!$invoice->hasPaymentEvidence()) {
            abort(404, 'Archivo de comprobante no encontrado.');
        }

        try {
            $filePath = $invoice->payment_evidence_path;
            
            if (!Storage::disk('payment_evidences')->exists($filePath)) {
                abort(404, 'Archivo no encontrado en el almacenamiento.');
            }

            $fullPath = Storage::disk('payment_evidences')->path($filePath);
            $originalName = 'comprobante_' . $invoice->full_number . '_' . pathinfo($filePath, PATHINFO_BASENAME);
            
            return response()->download($fullPath, $originalName);

        } catch (\Exception $e) {
            \Log::error('Error downloading payment evidence file', [
                'invoice_id' => $invoice->id,
                'file_path' => $invoice->payment_evidence_path,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Error al descargar el archivo.');
        }
    }

    /**
     * Check if user can view payment evidence
     */
    private function canViewPaymentEvidence(Invoice $invoice): bool
    {
        $user = auth()->user();
        
        // Administradores siempre pueden ver
        if ($user && $user->hasRole('admin')) {
            return true;
        }
        
        // El usuario que creó el pedido puede ver
        if ($user && $invoice->created_by === $user->id) {
            return true;
        }

        // Para usuarios invitados, verificar sesión
        if (!$user) {
            $guestInvoiceId = session()->get('guest_invoice_id');
            return $guestInvoiceId == $invoice->id;
        }

        return false;
    }
}