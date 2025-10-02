<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\DocumentSeries;
use App\Models\Product;
use App\Enums\DeliveryTimeSlot;
use App\Enums\DeliveryStatus;
use App\Enums\PaymentValidationStatus;
use App\Mail\PaymentReceivedMail;
use App\Rules\ValidDeliveryDate;
use App\Rules\ValidDeliveryTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Display checkout form
     */
    public function index()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío');
        }

        $total = $this->calculateTotal($cart);
        $deliveryTimeSlots = DeliveryTimeSlot::getOptions();
        $minDeliveryDate = Carbon::tomorrow()->format('Y-m-d');
        $maxDeliveryDate = Carbon::now()->addDays(30)->format('Y-m-d');

        return view('cart.checkout', compact('cart', 'total', 'deliveryTimeSlots', 'minDeliveryDate', 'maxDeliveryDate'));
    }

    /**
     * Process checkout and create order
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:500',
            'client_phone' => 'required|string|max:20',
            'client_email' => 'nullable|email|max:200',
            'client_address' => 'required|string|max:1000',
            'client_district' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,yape,plin,card,transfer',
            'payment_reference' => 'nullable|string|max:100',
            'observations' => 'nullable|string|max:500',
            // Delivery validation rules
            'delivery_date' => 'nullable|date|after:today|before:' . Carbon::now()->addDays(31)->format('Y-m-d'),
            'delivery_time_slot' => 'nullable|in:morning,afternoon,evening',
            'delivery_notes' => 'nullable|string|max:500',
            // Payment evidence validation
            'payment_evidence' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // 2MB max
            'payment_operation_number' => 'nullable|string|max:100',
            'client_payment_phone' => 'nullable|string|max:15',
        ]);

        // Additional delivery validations
        if ($request->has('delivery_date') && $request->delivery_date) {
            $deliveryDate = Carbon::parse($request->delivery_date);
            
            // Validate that delivery date is not on Sunday
            if ($deliveryDate->isSunday()) {
                return back()->withInput()->withErrors([
                    'delivery_date' => 'Las entregas no están disponibles los domingos.'
                ]);
            }
            
            // Validate time slot availability for the selected date
            if ($request->delivery_time_slot) {
                $timeSlot = DeliveryTimeSlot::from($request->delivery_time_slot);
                if (!$timeSlot->isAvailableOnDay($deliveryDate->format('l'))) {
                    return back()->withInput()->withErrors([
                        'delivery_time_slot' => 'El horario seleccionado no está disponible para el día elegido.'
                    ]);
                }
            }
            
            // If delivery date is provided, time slot is required
            if (!$request->delivery_time_slot) {
                return back()->withInput()->withErrors([
                    'delivery_time_slot' => 'Debe seleccionar un horario de entrega.'
                ]);
            }
        }

        // Validar comprobante para métodos que lo requieren
        $methodsRequiringEvidence = ['yape', 'plin', 'transfer'];
        if (in_array($validated['payment_method'], $methodsRequiringEvidence)) {
            if (!$request->hasFile('payment_evidence')) {
                return back()->withInput()->withErrors([
                    'payment_evidence' => 'Debe subir un comprobante de pago para este método.'
                ]);
            }
            
            if (empty($validated['payment_operation_number'])) {
                return back()->withInput()->withErrors([
                    'payment_operation_number' => 'Debe ingresar el número de operación.'
                ]);
            }
        }

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío');
        }

        try {
            DB::beginTransaction();

            // Get first company
            $company = \App\Models\Company::first();
            if (!$company) {
                throw new \Exception('No se encontró ninguna compañía configurada.');
            }

            // Get NV02 series for web orders
            $series = DocumentSeries::where('company_id', $company->id)
                ->where('series', 'NV02')
                ->firstOrFail();

            // Generate correlative number
            $number = $series->current_number + 1;
            $series->update(['current_number' => $number]);

            $fullNumber = 'NV02-' . str_pad($number, 8, '0', STR_PAD_LEFT);

            // Calculate totals
            $subtotal = $this->calculateTotal($cart);
            $total = $subtotal;

            // Handle payment evidence upload
            $paymentEvidencePath = null;
            if ($request->hasFile('payment_evidence')) {
                $file = $request->file('payment_evidence');
                $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $paymentEvidencePath = $file->storeAs('', $fileName, 'payment_evidences');
            }

            // Create invoice (order)
            $invoiceData = [
                'company_id' => $company->id,
                'document_series_id' => $series->id,
                'client_id' => null, // Pedidos web no requieren cliente registrado
                'series' => 'NV02',
                'number' => $number,
                'full_number' => $fullNumber,
                'document_type' => '09', // Nota de Venta
                'issue_date' => now()->toDateString(),
                'issue_time' => now()->toTimeString(),
                'currency_code' => 'PEN',
                'client_document_type' => '1',
                'client_document_number' => '-',
                'client_business_name' => $validated['client_name'],
                'client_address' => $validated['client_address'],
                'client_email' => $validated['client_email'],
                'subtotal' => $subtotal,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'payment_condition' => 'immediate',
                'status' => 'draft',
                'observations' => $validated['observations'],
                'created_by' => Auth::id(),
                // Payment validation fields
                'payment_evidence_path' => $paymentEvidencePath,
                'payment_operation_number' => $validated['payment_operation_number'] ?? null,
                'client_payment_phone' => $validated['client_payment_phone'] ?? null,
            ];

            // Add delivery information if provided
            if ($request->has('delivery_date') && $request->delivery_date) {
                $invoiceData['delivery_date'] = $request->delivery_date;
                $invoiceData['delivery_time_slot'] = DeliveryTimeSlot::from($request->delivery_time_slot);
                $invoiceData['delivery_notes'] = $request->delivery_notes;
                $invoiceData['delivery_status'] = DeliveryStatus::PROGRAMADO;
            }

            $invoice = Invoice::create($invoiceData);

            // Establecer estado de validación de pago
            $invoice->setPaymentValidationStatus();
            $invoice->saveQuietly();
            
            // Enviar email de confirmación si el cliente proporcionó email y hay evidencia de pago
            if ($invoice->client_email && ($paymentEvidencePath || $invoice->requiresPaymentValidation())) {
                try {
                    Mail::to($invoice->client_email)->send(new PaymentReceivedMail($invoice));
                } catch (\Exception $e) {
                    \Log::error('Error sending payment received email', [
                        'invoice_id' => $invoice->id,
                        'email' => $invoice->client_email,
                        'error' => $e->getMessage()
                    ]);
                    // No fallar el proceso si el email falla
                }
            }

            // Create details
            $lineNumber = 1;
            foreach ($cart as $item) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['id'],
                    'line_number' => $lineNumber++,
                    'product_code' => Product::find($item['id'])->code ?? 'WEB',
                    'description' => $item['name'],
                    'unit_code' => 'NIU',
                    'unit_description' => 'UNIDAD',
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'unit_value' => $item['price'],
                    'gross_amount' => $item['price'] * $item['quantity'],
                    'net_amount' => $item['price'] * $item['quantity'],
                    'line_total' => $item['price'] * $item['quantity'],
                ]);
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            // Store invoice ID in session for guest users
            if (!Auth::check()) {
                session()->put('guest_invoice_id', $invoice->id);
            }

            return redirect()->route('checkout.confirmation', $invoice->id);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Limpiar archivo de comprobante si se subió
            if ($paymentEvidencePath && Storage::disk('payment_evidences')->exists($paymentEvidencePath)) {
                Storage::disk('payment_evidences')->delete($paymentEvidencePath);
            }
            
            return back()->withInput()->with('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }

    /**
     * Show order confirmation
     */
    public function confirmation($invoiceId)
    {
        // For authenticated users, verify the invoice belongs to them
        if (Auth::check()) {
            $invoice = Invoice::with('details.product')
                ->where('created_by', Auth::id())
                ->findOrFail($invoiceId);
        } else {
            // For guest users, verify invoice ID matches session
            $guestInvoiceId = session()->get('guest_invoice_id');

            if ($guestInvoiceId != $invoiceId) {
                abort(403, 'No tienes permiso para ver este pedido.');
            }

            $invoice = Invoice::with('details.product')->findOrFail($invoiceId);
        }

        return view('cart.confirmation', compact('invoice'));
    }

    /**
     * Show user's orders
     */
    public function myOrders()
    {
        $orders = Invoice::where('created_by', Auth::id())
            ->where('series', 'NV02')
            ->with('details')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('account.orders', compact('orders'));
    }

    /**
     * Calculate cart total
     */
    private function calculateTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    /**
     * Get available delivery time slots for a specific date
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after:today'
        ]);

        $date = Carbon::parse($request->date);
        
        // Check if date is Sunday (no deliveries)
        if ($date->isSunday()) {
            return response()->json([
                'available' => false,
                'message' => 'No hay entregas disponibles los domingos',
                'slots' => []
            ]);
        }

        $availableSlots = DeliveryTimeSlot::availableForDate($date);
        
        $slots = collect($availableSlots)->map(function($slot) {
            return [
                'value' => $slot->value,
                'label' => $slot->label(),
                'time_range' => $slot->timeRange()
            ];
        })->values();

        return response()->json([
            'available' => true,
            'slots' => $slots
        ]);
    }
}
