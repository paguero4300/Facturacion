<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Pago Requerida</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .order-summary { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .warning-box { background: #fef3c7; border: 1px solid #f59e0b; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; background: #f59e0b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 15px 0; }
        .info-box { background: #dbeafe; border: 1px solid #93c5fd; padding: 15px; border-radius: 6px; margin: 15px 0; }
        .footer { text-align: center; color: #6b7280; font-size: 14px; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Verificación Requerida</h1>
            <p>Necesitamos verificar tu comprobante de pago</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $invoice->client_business_name }}</strong>,</p>
            
            <div class="warning-box">
                <h2 style="color: #92400e; margin-top: 0;">🔍 Necesitamos revisar tu pago</h2>
                <p style="color: #b45309;">Hemos revisado el comprobante que enviaste, pero necesitamos que nos proporciones información adicional o un nuevo comprobante para procesar tu pedido.</p>
            </div>
            
            <div class="order-summary">
                <h3>📋 Detalles del Pedido</h3>
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
            </div>
            
            @if(!empty($rejectionReason))
                <div class="info-box">
                    <p><strong>📝 Motivo de la solicitud:</strong></p>
                    <p style="font-style: italic;">{{ $rejectionReason }}</p>
                </div>
            @endif
            
            <div class="info-box">
                <p><strong>🔄 ¿Qué puedes hacer?</strong></p>
                <ul>
                    <li>Verifica que el comprobante esté completo y sea legible</li>
                    <li>Asegúrate de que el monto coincida con el total de tu pedido</li>
                    <li>Confirma que el número de operación sea correcto</li>
                    @if($invoice->payment_method === 'transfer')
                        <li>Para transferencias: incluye el comprobante bancario completo</li>
                    @endif
                    @if(in_array($invoice->payment_method, ['yape', 'plin']))
                        <li>Para {{ ucfirst($invoice->payment_method) }}: asegúrate de que se vea claramente la operación exitosa</li>
                    @endif
                </ul>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Envía un nuevo comprobante o contáctanos para resolver cualquier duda.</strong></p>
                <p style="color: #6b7280; font-size: 14px;">Puedes responder a este email con el nuevo comprobante o comunicarte con nosotros directamente.</p>
            </div>
            
            <div class="info-box">
                <p><strong>📞 ¿Necesitas ayuda?</strong></p>
                <p>Si tienes dudas sobre este proceso, no dudes en contactarnos. Estamos aquí para ayudarte a completar tu pedido exitosamente.</p>
            </div>
            
            <p>Lamentamos cualquier inconveniente y agradecemos tu comprensión.</p>
            
            <p><strong>Atentamente,<br>El equipo de Tu Empresa</strong></p>
        </div>
        
        <div class="footer">
            <p>Este es un mensaje automático, pero puedes responder si necesitas ayuda.</p>
            <p>© {{ date('Y') }} Tu Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>