<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - {{ $invoice->full_number }}</title>
    <style>
        /* Reset y configuración base para ticket 80mm */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            width: 72mm; /* 80mm - márgenes */
            margin: 0 auto;
            padding: 2mm;
        }
        
        /* Encabezado de la empresa */
        .header {
            text-align: center;
            margin-bottom: 3mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .company-info {
            font-size: 8px;
            line-height: 1.1;
        }
        
        /* Información del documento */
        .document-info {
            text-align: center;
            margin: 3mm 0;
            padding: 2mm 0;
            border-bottom: 1px dashed #000;
        }
        
        .document-type {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .document-number {
            font-size: 9px;
            font-weight: bold;
        }
        
        /* Información del cliente */
        .client-info {
            margin: 3mm 0;
            font-size: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
        }
        
        .client-row {
            margin-bottom: 0.5mm;
        }
        
        /* Tabla de productos */
        .products {
            margin: 3mm 0;
        }
        
        .product-header {
            font-size: 8px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }
        
        .product-item {
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .product-name {
            font-weight: bold;
            margin-bottom: 0.5mm;
        }
        
        .product-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }
        
        /* Totales */
        .totals {
            margin-top: 3mm;
            border-top: 1px dashed #000;
            padding-top: 2mm;
            font-size: 8px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5mm;
        }
        
        .total-final {
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 1mm;
            margin-top: 1mm;
        }
        
        /* Pie de página */
        .footer {
            margin-top: 4mm;
            text-align: center;
            font-size: 7px;
            border-top: 1px dashed #000;
            padding-top: 2mm;
        }
        
        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .small { font-size: 7px; }
        
        /* Espaciado */
        .mb-1 { margin-bottom: 1mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mt-2 { margin-top: 2mm; }
    </style>
</head>
<body>
    <!-- Encabezado de la empresa -->
    <div class="header">
        <div class="company-name">{{ $invoice->company->business_name }}</div>
        @if($invoice->company->commercial_name && $invoice->company->commercial_name !== $invoice->company->business_name)
            <div class="small">{{ $invoice->company->commercial_name }}</div>
        @endif
        <div class="company-info">
            <div>RUC: {{ $invoice->company->ruc }}</div>
            <div>{{ $invoice->company->address }}</div>
            @if($invoice->company->district)
                <div>{{ $invoice->company->district }}@if($invoice->company->province), {{ $invoice->company->province }}@endif</div>
            @endif
            @if($invoice->company->phone)
                <div>Teléfono: {{ $invoice->company->phone }}</div>
            @endif
            @if($invoice->company->email)
                <div>{{ $invoice->company->email }}</div>
            @endif
        </div>
    </div>

    <!-- Información del documento -->
    <div class="document-info">
        <div class="document-type">
            @if($invoice->document_type === '01')
                FACTURA ELECTRÓNICA
            @elseif($invoice->document_type === '03')
                BOLETA DE VENTA ELECTRÓNICA
            @elseif($invoice->document_type === '07')
                NOTA DE CRÉDITO ELECTRÓNICA
            @elseif($invoice->document_type === '08')
                NOTA DE DÉBITO ELECTRÓNICA
            @elseif($invoice->document_type === '09')
                NOTA DE VENTA - USO INTERNO
            @else
                COMPROBANTE ELECTRÓNICO
            @endif
        </div>
        <div class="document-number">{{ $invoice->full_number }}</div>
        <div class="small mt-2">
            Fecha: {{ $invoice->issue_date->format('d/m/Y') }}
            @if($invoice->issue_time)
                {{ $invoice->issue_time }}
            @endif
        </div>
    </div>

    <!-- Información del cliente -->
    <div class="client-info">
        <div class="client-row">
            <strong>Cliente:</strong> {{ $invoice->client_business_name }}
        </div>
        <div class="client-row">
            <strong>
                @if($invoice->client_document_type === '6')
                    RUC:
                @elseif($invoice->client_document_type === '1')
                    DNI:
                @elseif($invoice->client_document_type === '4')
                    C.E.:
                @elseif($invoice->client_document_type === '7')
                    Pasaporte:
                @else
                    Documento:
                @endif
            </strong> 
            {{ $invoice->client_document_number }}
        </div>
        @if($invoice->client_address)
            <div class="client-row">
                <strong>Dirección:</strong> {{ $invoice->client_address }}
            </div>
        @endif
        @if($invoice->client_email)
            <div class="client-row">
                <strong>Email:</strong> {{ $invoice->client_email }}
            </div>
        @endif
    </div>

    <!-- Productos -->
    <div class="products">
        <div class="product-header">
            DESCRIPCIÓN
        </div>
        
        @foreach($invoice->details as $detail)
            <div class="product-item">
                <div class="product-name">{{ $detail->description ?? $detail->product->name }}</div>
                <div class="product-details">
                    <span>{{ number_format($detail->quantity, 0) }} x {{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($detail->unit_price, 2) }}</span>
                    <span class="bold">{{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($detail->line_total, 2) }}</span>
                </div>
                @if($detail->product_code || ($detail->product && $detail->product->code))
                    <div class="small">Código: {{ $detail->product_code ?? $detail->product->code }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Totales -->
    <div class="totals">
        @if($invoice->subtotal > 0)
            <div class="total-row">
                <span>Subtotal:</span>
                <span>{{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($invoice->subtotal, 2) }}</span>
            </div>
        @endif
        
        @if($invoice->igv_amount > 0)
            <div class="total-row">
                <span>IGV ({{ number_format($invoice->igv_rate * 100, 0) }}%):</span>
                <span>{{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($invoice->igv_amount, 2) }}</span>
            </div>
        @endif
        
        @if($invoice->total_discounts > 0)
            <div class="total-row">
                <span>Descuento:</span>
                <span>-{{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($invoice->total_discounts, 2) }}</span>
            </div>
        @endif
        
        <div class="total-row total-final">
            <span>TOTAL:</span>
            <span>{{ $invoice->currency_code === 'USD' ? 'US$' : 'S/' }} {{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    <!-- Información de pago -->
    @if($invoice->payment_method || $invoice->payment_condition)
        <div class="totals mt-2">
            @if($invoice->payment_method)
                <div class="total-row">
                    <span>Forma de Pago:</span>
                    <span>
                        @if($invoice->payment_method === 'cash')
                            Efectivo
                        @elseif($invoice->payment_method === 'card')
                            Tarjeta
                        @elseif($invoice->payment_method === 'transfer')
                            Transferencia
                        @elseif($invoice->payment_method === 'check')
                            Cheque
                        @else
                            {{ ucfirst($invoice->payment_method) }}
                        @endif
                    </span>
                </div>
            @endif
            
            @if($invoice->payment_condition === 'credit' && $invoice->credit_days > 0)
                <div class="total-row">
                    <span>Condición:</span>
                    <span>Crédito {{ $invoice->credit_days }} días</span>
                </div>
            @elseif($invoice->payment_condition === 'immediate')
                <div class="total-row">
                    <span>Condición:</span>
                    <span>Contado</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Pie de página -->
    <div class="footer">
        <div class="mb-1">¡Gracias por su compra!</div>
        @if($invoice->document_type === '09')
            <div class="small">
                NOTA DE VENTA - SIN VALIDEZ TRIBUTARIA
            </div>
            <div class="small">
                Documento solo para uso interno
            </div>
        @else
            <div class="small">
                Representación impresa de comprobante electrónico
            </div>
            @if($invoice->sunat_status === 'accepted')
                <div class="small">
                    Autorizado mediante resolución de intendencia
                </div>
            @endif
        @endif
        <div class="small mt-2">
            Impreso: {{ now()->format('d/m/Y H:i:s') }}
        </div>
    </div>
</body>
</html>