<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Recibido</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ec4899 0%, #f43f5e 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .order-summary { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ec4899; }
        .button { display: inline-block; background: #ec4899; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
        .info-box { background: #dbeafe; border: 1px solid #93c5fd; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Comprobante Recibido</h1>
            <p>Hemos recibido tu comprobante de pago</p>
        </div>
        
        <div class="content">
            <p>¡Hola <strong>{{ $invoice->client_business_name }}</strong>!</p>
            
            <p>Hemos recibido tu comprobante de pago para el pedido <strong>{{ $invoice->full_number }}</strong>. Nuestro equipo está revisando la información y te confirmaremos en breve.</p>
            
            <div class="order-summary">
                <h3>📋 Resumen del Pedido</h3>
                <p><strong>Número de Pedido:</strong> {{ $invoice->full_number }}</p>
                <p><strong>Fecha:</strong> {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Total:</strong> S/ {{ number_format($invoice->total_amount, 2) }}</p>
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
                <p><strong>⏰ ¿Qué sigue?</strong></p>
                <ul>
                    <li>Nuestro equipo revisará tu comprobante en las próximas horas</li>
                    <li>Te enviaremos una confirmación una vez aprobado el pago</li>
                    <li>Procederemos a preparar tu pedido para entrega</li>
                </ul>
            </div>
            
            <p>Si tienes alguna pregunta o necesitas hacer algún cambio, no dudes en contactarnos.</p>
            
            <p>¡Gracias por tu compra! 🎉</p>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático, por favor no respondas a este email.</p>
            <p>© {{ date('Y') }} Tu Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>