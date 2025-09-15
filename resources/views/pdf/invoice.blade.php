<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->document_type === '01' ? 'Factura' : 'Boleta' }} {{ $invoice->full_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: white;
            padding: 10mm;
        }
        
        .invoice-container {
            max-width: 190mm;
            margin: 0 auto;
        }
        
        /* === ENCABEZADO COMPACTO === */
        .header-table {
            width: 100%;
            border: 2px solid #000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .header-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        
        .company-cell {
            width: 60%;
        }
        
        .document-cell {
            width: 40%;
            text-align: center;
        }
        
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .company-details {
            font-size: 9px;
            line-height: 1.4;
        }
        
        .document-type {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        
        .document-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .document-date {
            font-size: 9px;
        }
        
        /* === INFORMACIÓN DEL CLIENTE === */
        .client-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .client-table td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 9px;
        }
        
        .client-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        /* === TABLA DE PRODUCTOS === */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .details-table th,
        .details-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }
        
        .details-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .details-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        
        /* === TOTALES === */
        .totals-container {
            float: right;
            width: 250px;
            border: 1px solid #000;
            margin-bottom: 8px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 4px 8px;
            font-size: 10px;
            border-bottom: 1px solid #ccc;
        }
        
        .totals-table .total-final {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 11px;
        }
        
        /* === IMPORTE EN LETRAS === */
        .amount-words {
            clear: both;
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 8px;
            background-color: #f9f9f9;
        }
        
        .amount-words-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .amount-text {
            font-size: 10px;
            font-style: italic;
        }
        
        /* === CRONOGRAMA === */
        .payment-schedule {
            margin-bottom: 8px;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }
        
        .schedule-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        /* === OBSERVACIONES === */
        .observations {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 8px;
            background-color: #f9f9f9;
        }
        
        .observations-title {
            font-weight: bold;
            margin-bottom: 4px;
            font-size: 10px;
        }
        
        .observations-text {
            font-size: 9px;
        }
        
        /* === PIE DE PÁGINA === */
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 6px;
        }
        
        .footer p {
            margin-bottom: 2px;
        }
        
        /* === BADGES SIMPLES === */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-paid { background-color: #e0e0e0; }
        .status-pending { background-color: #f0f0f0; }
        .status-partial { background-color: #f5f5f5; }
        
        /* === UTILIDADES === */
        .font-bold { font-weight: bold; }
        .small { font-size: 8px; }
        
        /* === RESPONSIVE === */
        @media print {
            body { padding: 5mm; }
            .invoice-container { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- === ENCABEZADO COMPACTO === -->
        <table class="header-table">
            <tr>
                <td class="company-cell">
                    <div class="company-name">{{ $invoice->company->business_name }}</div>
                    <div class="company-details">
                        @if($invoice->company->commercial_name && $invoice->company->commercial_name !== $invoice->company->business_name)
                            <strong>Nombre Comercial:</strong> {{ $invoice->company->commercial_name }}<br>
                        @endif
                        <strong>RUC:</strong> {{ $invoice->company->ruc }}<br>
                        <strong>Dirección:</strong> {{ $invoice->company->address }}<br>
                        @if($invoice->company->district || $invoice->company->province)
                            {{ $invoice->company->district }}{{ $invoice->company->province ? ', ' . $invoice->company->province : '' }}<br>
                        @endif
                        @if($invoice->company->phone)
                            <strong>Teléfono:</strong> {{ $invoice->company->phone }}
                        @endif
                        @if($invoice->company->email)
                            | <strong>Email:</strong> {{ $invoice->company->email }}
                        @endif
                    </div>
                </td>
                <td class="document-cell">
                    <div class="document-type">
                        @switch($invoice->document_type)
                            @case('01')
                                Factura Electrónica
                                @break
                            @case('03')
                                Boleta de Venta Electrónica
                                @break
                            @case('07')
                                Nota de Crédito Electrónica
                                @break
                            @case('08')
                                Nota de Débito Electrónica
                                @break
                            @case('09')
                                Nota de Venta - Uso Interno
                                @break
                            @default
                                Comprobante Electrónico
                        @endswitch
                    </div>
                    <div class="document-number">{{ $invoice->full_number }}</div>
                    <div class="document-date">
                        Fecha: {{ $invoice->issue_date->format('d/m/Y') }}
                        @if($invoice->issue_time)
                            {{ $invoice->issue_time }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <!-- === INFORMACIÓN DEL CLIENTE === -->
        <table class="client-table">
            <tr>
                <td class="client-header" colspan="4">DATOS DEL CLIENTE</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong> {{ $invoice->client_business_name }}</td>
                <td><strong>{{ $invoice->client_document_type === '6' ? 'RUC' : 'DNI' }}:</strong> {{ $invoice->client_document_number }}</td>
                <td><strong>Fecha Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}</td>
                <td><strong>Condición:</strong> {{ $invoice->payment_condition === 'immediate' ? 'Contado' : 'Crédito' }}</td>
            </tr>
            <tr>
                <td><strong>Dirección:</strong> {{ $invoice->client_address ?: 'No especificada' }}</td>
                <td><strong>Forma Pago:</strong> 
                    @switch($invoice->payment_method)
                        @case('cash') Efectivo @break
                        @case('card') Tarjeta @break
                        @case('transfer') Transferencia @break
                        @case('check') Cheque @break
                        @default {{ ucfirst($invoice->payment_method) }}
                    @endswitch
                </td>
                <td><strong>Moneda:</strong> {{ $invoice->currency_code === 'PEN' ? 'Soles' : 'Dólares' }}</td>
                @if($invoice->due_date)
                    <td><strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}</td>
                @else
                    <td>&nbsp;</td>
                @endif
            </tr>
        </table>

        <!-- === PRODUCTOS/SERVICIOS === -->
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 6%;">Cant.</th>
                    <th style="width: 8%;">Unid.</th>
                    <th style="width: 10%;">Código</th>
                    <th style="width: 40%;">Descripción</th>
                    <th style="width: 12%;">P. Unit.</th>
                    <th style="width: 10%;">Dscto.</th>
                    <th style="width: 14%;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                    <tr>
                        <td class="text-center">{{ number_format($detail->quantity, 0) }}</td>
                        <td class="text-center">{{ $detail->unit_description ?: $detail->unit_code }}</td>
                        <td class="text-center">{{ $detail->product_code ?: '-' }}</td>
                        <td class="text-left">{{ $detail->description }}</td>
                        <td class="text-right">{{ number_format($detail->unit_price, 2) }}</td>
                        <td class="text-right">{{ number_format($detail->line_discount_amount, 2) }}</td>
                        <td class="text-right">{{ number_format($detail->line_total, 2) }}</td>
                    </tr>
                @endforeach
                
                <!-- Rellenar filas vacías -->
                @for($i = count($invoice->details); $i < 12; $i++)
                    <tr>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- === TOTALES === -->
        <div class="totals-container">
            <table class="totals-table">
                @php
                    $subtotal = $invoice->subtotal;
                    $igv = $invoice->igv_amount;
                    $total = $invoice->total_amount;
                    $symbol = $invoice->currency_code === 'USD' ? 'US$' : 'S/';
                @endphp
                
                <tr>
                    <td>Sub Total:</td>
                    <td class="text-right">{{ $symbol }} {{ number_format($subtotal, 2) }}</td>
                </tr>
                
                @if($invoice->total_discounts > 0)
                    <tr>
                        <td>Descuentos:</td>
                        <td class="text-right">-{{ $symbol }} {{ number_format($invoice->total_discounts, 2) }}</td>
                    </tr>
                @endif
                
                <tr>
                    <td>IGV ({{ number_format($invoice->igv_rate * 100, 0) }}%):</td>
                    <td class="text-right">{{ $symbol }} {{ number_format($igv, 2) }}</td>
                </tr>
                
                <tr class="total-final">
                    <td>TOTAL:</td>
                    <td class="text-right">{{ $symbol }} {{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- === IMPORTE EN LETRAS === -->
        <div class="amount-words">
            <div class="amount-words-title">SON:</div>
            <div class="amount-text">
                @php
                    $currencyWord = $invoice->currency_code === 'USD' ? 'DÓLARES AMERICANOS' : 'SOLES';
                    try {
                        $fmt = new NumberFormatter('es_PE', NumberFormatter::SPELLOUT);
                        $int = (int) floor($total);
                        $cents = (int) round(($total - $int) * 100);
                        $words = strtoupper($fmt->format($int));
                        $centsStr = str_pad((string) $cents, 2, '0', STR_PAD_LEFT);
                        echo $words . ' CON ' . $centsStr . '/100 ' . $currencyWord;
                    } catch (Throwable $e) {
                        echo number_format($total, 2, '.', ',') . ' ' . $currencyWord;
                    }
                @endphp
            </div>
        </div>

        <!-- === CRONOGRAMA DE PAGOS === -->
        @if($invoice->payment_condition === 'credit' && $invoice->paymentInstallments->count() > 0)
            <div class="payment-schedule">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th colspan="4">CRONOGRAMA DE PAGOS</th>
                        </tr>
                        <tr>
                            <th>Cuota</th>
                            <th>Fecha Vencimiento</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->paymentInstallments as $installment)
                            <tr>
                                <td class="text-center">{{ $installment->installment_number }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($installment->due_date)->format('d/m/Y') }}</td>
                                <td class="text-right">{{ $symbol }} {{ number_format($installment->amount, 2) }}</td>
                                <td class="text-center">
                                    @switch($installment->status)
                                        @case('paid')
                                            <span class="status-badge status-paid">PAGADO</span>
                                            @break
                                        @case('partial')
                                            <span class="status-badge status-partial">PARCIAL</span>
                                            @break
                                        @default
                                            <span class="status-badge status-pending">PENDIENTE</span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- === OBSERVACIONES === -->
        @if($invoice->observations)
            <div class="observations">
                <div class="observations-title">OBSERVACIONES:</div>
                <div class="observations-text">{{ $invoice->observations }}</div>
            </div>
        @endif

        <!-- === PIE DE PÁGINA === -->
        <div class="footer">
            @if($invoice->document_type === '09')
                <p><strong>NOTA DE VENTA - DOCUMENTO SIN VALIDEZ TRIBUTARIA</strong></p>
                <p>Este documento es solo para uso interno y no tiene validez ante SUNAT.</p>
            @else
                <p>Representación impresa del comprobante electrónico.</p>
                @if($invoice->sunat_status === 'accepted')
                    <p><strong>Estado SUNAT: ACEPTADO</strong></p>
                @elseif($invoice->sunat_status === 'pending')
                    <p><strong>Estado SUNAT: PENDIENTE</strong></p>
                @endif
            @endif
            <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>