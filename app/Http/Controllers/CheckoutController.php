<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\DocumentSeries;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

        return view('cart.checkout', compact('cart', 'total'));
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
        ]);

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

            // Create invoice (order)
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'document_series_id' => $series->id,
                'client_id' => null, // Pedidos web no requieren cliente registrado
                'series' => 'NV02',
                'number' => $number,
                'full_number' => $fullNumber,
                'document_type' => '00', // Nota de Venta
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
            ]);

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
}
