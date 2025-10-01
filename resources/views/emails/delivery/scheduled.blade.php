<x-mail::message>
# 📦 Entrega Programada

Estimado/a **{{ $customerName }}**,

Nos complace informarle que su pedido **{{ $orderNumber }}** ha sido confirmado y programado para entrega.

## 📅 Detalles de la Entrega

- **Fecha**: {{ $deliveryDate->format('d/m/Y') }}
- **Horario**: {{ $deliveryTimeSlot->label() }}
- **Dirección**: {{ $deliveryAddress }}

@if($deliveryNotes)
## 📝 Instrucciones Especiales
{{ $deliveryNotes }}
@endif

## 📱 ¿Qué sigue?

1. Le contactaremos cuando el pedido esté en camino
2. Asegúrese de estar disponible en el horario programado
3. Tenga a mano su documento de identidad para la entrega

<x-mail::button :url="route('account.orders')" color="success">
Ver Mi Pedido
</x-mail::button>

Si necesita reprogramar su entrega o tiene alguna consulta, no dude en contactarnos.

Gracias por su compra,<br>
{{ config('app.name') }}
</x-mail::message>
