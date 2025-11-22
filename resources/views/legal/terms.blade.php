@extends('layouts.app')

@section('title', 'Términos y Condiciones - Detalles y Más')

@section('content')
    <!-- Page Header -->
    <div class="relative bg-gradient-to-br from-[var(--fondo-principal)] via-white to-orange-50 py-12 overflow-hidden">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-[var(--enlaces-titulos)] mb-3">
                Términos y Condiciones
            </h1>
            <p class="text-base text-[var(--texto-principal)]">
                Última actualización: {{ now()->format('d/m/Y') }}
            </p>
        </div>
    </div>

    <!-- Content -->
    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 md:p-12">
                <div class="prose prose-gray max-w-none">
                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">1. Aceptación de los Términos</h2>
                    <p class="mb-6">
                        Al acceder y utilizar nuestro sitio web y realizar compras en <strong>Detalles y Más</strong>, 
                        usted acepta estar sujeto a estos Términos y Condiciones. Si no está de acuerdo con alguna 
                        parte de estos términos, no debe utilizar nuestros servicios.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">2. Productos y Servicios</h2>
                    <p class="mb-4">
                        Ofrecemos arreglos florales, regalos y productos relacionados. Nos reservamos el derecho de:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li>Modificar los productos y precios sin previo aviso</li>
                        <li>Limitar las cantidades disponibles para compra</li>
                        <li>Suspender o cancelar pedidos en caso de inconsistencias o fraude</li>
                        <li>Sustituir flores o elementos decorativos por alternativas similares si no están disponibles, 
                            manteniendo el valor y estética del arreglo</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">3. Pedidos y Pagos</h2>
                    <p class="mb-4">
                        <strong>3.1 Proceso de Compra:</strong> Al realizar un pedido, usted acepta proporcionar 
                        información completa y precisa.
                    </p>
                    <p class="mb-4">
                        <strong>3.2 Métodos de Pago:</strong> Aceptamos transferencias bancarias, Yape, tarjetas 
                        de crédito/débito y pago en efectivo contra entrega.
                    </p>
                    <p class="mb-4">
                        <strong>3.3 Confirmación:</strong> Los pedidos quedan confirmados una vez validado el pago 
                        o, en caso de pago contra entrega, al aceptar el pedido.
                    </p>
                    <p class="mb-6">
                        <strong>3.4 Validación de Pago:</strong> Para pagos con transferencia, Yape o Plin, 
                        el pedido será procesado una vez que validemos el comprobante de pago enviado.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">4. Entregas</h2>
                    <p class="mb-4">
                        <strong>4.1 Áreas de Cobertura:</strong> Realizamos entregas en Lima Metropolitana. 
                        Las zonas y horarios específicos están sujetos a disponibilidad.
                    </p>
                    <p class="mb-4">
                        <strong>4.2 Costos de Envío:</strong> Los costos de envío se coordinan según la zona 
                        de entrega y se informarán antes de confirmar el pedido.
                    </p>
                    <p class="mb-4">
                        <strong>4.3 Tiempos de Entrega:</strong> Las entregas se realizan en los horarios 
                        programados (mañana, tarde, noche) de lunes a sábado. No realizamos entregas los domingos.
                    </p>
                    <p class="mb-6">
                        <strong>4.4 Responsabilidad:</strong> No nos hacemos responsables por demoras causadas 
                        por circunstancias fuera de nuestro control (clima, tráfico, destinatario ausente).
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">5. Políticas de Devolución y Reembolso</h2>
                    <p class="mb-4">
                        <strong>5.1 Productos Perecibles:</strong> Debido a la naturaleza perecedera de nuestros 
                        productos (flores frescas), no aceptamos devoluciones excepto en casos de:
                    </p>
                    <ul class="list-disc pl-6 mb-4 space-y-2">
                        <li>Productos dañados o en mal estado al momento de la entrega</li>
                        <li>Error en el pedido entregado</li>
                        <li>Incumplimiento significativo de lo ofrecido</li>
                    </ul>
                    <p class="mb-4">
                        <strong>5.2 Plazo de Reclamo:</strong> Cualquier reclamo debe reportarse dentro de las 
                        24 horas siguientes a la entrega, con evidencia fotográfica.
                    </p>
                    <p class="mb-6">
                        <strong>5.3 Reembolsos:</strong> Los reembolsos se procesarán en un plazo de 7 a 14 días 
                        hábiles después de aprobar el reclamo.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">6. Uso del Sitio Web</h2>
                    <p class="mb-4">
                        Usted se compromete a utilizar nuestro sitio web de manera lícita y no realizar actividades que:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li>Infrinjan derechos de terceros</li>
                        <li>Transmitan virus o código malicioso</li>
                        <li>Intenten obtener acceso no autorizado</li>
                        <li>Realicen actividades comerciales no autorizadas</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">7. Propiedad Intelectual</h2>
                    <p class="mb-6">
                        Todo el contenido del sitio (imágenes, textos, logos, diseños) es propiedad de Detalles y Más 
                        o sus licenciantes. No está permitido copiar, reproducir o distribuir sin autorización expresa.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">8. Limitación de Responsabilidad</h2>
                    <p class="mb-6">
                        No seremos responsables por daños indirectos, incidentales o consecuentes que puedan surgir 
                        del uso de nuestros productos o servicios, más allá de lo establecido por la ley peruana.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">9. Modificaciones</h2>
                    <p class="mb-6">
                        Nos reservamos el derecho de modificar estos Términos y Condiciones en cualquier momento. 
                        Los cambios entrarán en vigor una vez publicados en el sitio web.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">10. Ley Aplicable</h2>
                    <p class="mb-6">
                        Estos términos se rigen por las leyes de la República del Perú. Cualquier disputa será 
                        resuelta en los tribunales de Lima, Perú.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">11. Contacto</h2>
                    <p class="mb-2">
                        Para consultas sobre estos Términos y Condiciones:
                    </p>
                    <ul class="list-none space-y-2 mb-6">
                        <li><strong>WhatsApp:</strong> <a href="https://wa.me/51941492316" class="text-[var(--azul-primario)] hover:underline">941 492 316</a></li>
                        <li><strong>Dirección:</strong> Lima, Perú</li>
                    </ul>

                    <div class="mt-8 p-4 bg-orange-50 border-l-4 border-[var(--naranja)] rounded">
                        <p class="text-sm text-gray-700">
                            <strong>Nota importante:</strong> Al realizar un pedido en nuestro sitio web, 
                            usted confirma que ha leído, entendido y aceptado estos Términos y Condiciones 
                            en su totalidad.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
