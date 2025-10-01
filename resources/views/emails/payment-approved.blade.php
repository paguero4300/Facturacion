<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Aprobado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .order-summary { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #10b981; }
        .success-box { background: #d1fae5; border: 1px solid #34d399; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; }
        .button { display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
        .info-box { background: #dbeafe; border: 1px solid #93c5fd; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ ¡Pago Aprobado!</h1>
            <p>Tu pago ha sido verificado exitosamente</p>
        </div>
        
        <div class="content">
            <p>¡Excelente noticia, <strong>{{ $invoice->client_business_name }}</strong>!</p>
            
            <div class="success-box">
                <h2 style="color: #065f46; margin-top: 0;">🎉 Tu pago ha sido aprobado</h2>
                <p style="color: #047857; margin-bottom: 0;">Ya puedes estar tranquilo, hemos verificado tu pago y todo está en orden.</p>
            </div>
            
            <div class="order-summary">
                <h3>📋 Detalles del Pedido</h3>
                <p><strong>Número de Pedido:</strong> {{ $invoice->full_number }}</p>
                <p><strong>Fecha de Pedido:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Fecha de Aprobación:</strong> {{ $invoice->payment_validated_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</p>
                <p><strong>Total Pagado:</strong> S/ {{ number_format($invoice->total_amount, 2) }}</p>
                <p><strong>Método de Pago:</strong> 
                    @switch($invoice->payment_method)
                        @case('yape') Yape @break
                        @case('plin') Plin @break
                        @case('transfer') Transferencia Bancaria @break
                        @default {{ ucfirst($invoice->payment_method) }}
                    @endswitch
                </p>
                @if($invoice->payment_operation_number)
                    <p><strong>Número de Operación:</strong> {{ $invoice->payment_operation_number }}</p>
                @endif
            </div>
            
            <div class="info-box">
                <p><strong>📦 ¿Qué sigue ahora?</strong></p>
                <ul>
                    <li><strong>Preparación:</strong> Ya estamos preparando tu pedido</li>
                    @if($invoice->hasDeliveryScheduled())
                        <li><strong>Entrega Programada:</strong> {{ $invoice->delivery_date->format('d/m/Y') }} 
                            @if($invoice->delivery_time_slot)
                                ({{ $invoice->delivery_time_slot->timeRange() }})
                            @endif
                        </li>
                    @else
                        <li><strong>Entrega:</strong> Te contactaremos para coordinar la entrega</li>
                    @endif
                    <li><strong>Seguimiento:</strong> Te mantendremos informado del estado de tu pedido</li>
                </ul>
            </div>
            
            @if($invoice->delivery_notes)
                <div class="info-box">
                    <p><strong>📝 Notas de Entrega:</strong></p>
                    <p style="font-style: italic;">{{ $invoice->delivery_notes }}</p>
                </div>
            @endif
            
            <p>Si tienes alguna pregunta o necesitas hacer algún cambio, no dudes en contactarnos.</p>
            
            <p><strong>¡Gracias por tu confianza y tu compra! 🛍️</strong></p>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este email.</p>
            <p>© {{ date('Y') }} Tu Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>