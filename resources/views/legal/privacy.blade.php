@extends('layouts.app')

@section('title', 'Política de Privacidad - Detalles y Más')

@section('content')
    <!-- Page Header -->
    <div class="relative bg-gradient-to-br from-[var(--fondo-principal)] via-white to-blue-50 py-12 overflow-hidden">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-[var(--enlaces-titulos)] mb-3">
                Política de Privacidad
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
                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">1. Introducción</h2>
                    <p class="mb-6">
                        En <strong>Detalles y Más</strong>, nos comprometemos a proteger su privacidad y manejar 
                        sus datos personales de manera responsable. Esta Política de Privacidad explica cómo 
                        recopilamos, usamos, compartimos y protegemos su información personal.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">2. Información que Recopilamos</h2>
                    <p class="mb-4">
                        <strong>2.1 Información que Usted Proporciona:</strong>
                    </p>
                    <ul class="list-disc pl-6 mb-4 space-y-2">
                        <li>Nombre completo y apellidos</li>
                        <li>Dirección de correo electrónico</li>
                        <li>Número de teléfono</li>
                        <li>Dirección de entrega</li>
                        <li>Información de pago (procesada de forma segura a través de proveedores externos)</li>
                        <li>Observaciones o instrucciones especiales para el pedido</li>
                    </ul>
                    <p class="mb-4">
                        <strong>2.2 Información Recopilada Automáticamente:</strong>
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li>Dirección IP</li>
                        <li>Tipo de navegador y dispositivo</li>
                        <li>Páginas visitadas en nuestro sitio</li>
                        <li>Fecha y hora de acceso</li>
                        <li>Cookies y tecnologías similares</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">3. Cómo Utilizamos su Información</h2>
                    <p class="mb-4">
                        Utilizamos la información recopilada para:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li><strong>Procesar pedidos:</strong> Gestionar su compra y coordinar la entrega</li>
                        <li><strong>Comunicaciones:</strong> Enviar confirmaciones de pedido, actualizaciones de estado 
                            y notificaciones relacionadas con su compra</li>
                        <li><strong>Atención al cliente:</strong> Responder a sus consultas y resolver problemas</li>
                        <li><strong>Mejora del servicio:</strong> Analizar el uso del sitio para mejorar la experiencia</li>
                        <li><strong>Marketing:</strong> Enviar promociones y ofertas (solo con su consentimiento previo)</li>
                        <li><strong>Cumplimiento legal:</strong> Cumplir con obligaciones legales y fiscales (emisión de comprobantes)</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">4. Base Legal para el Procesamiento</h2>
                    <p class="mb-4">
                        Procesamos sus datos personales bajo las siguientes bases legales:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li><strong>Ejecución de contrato:</strong> Para procesar y entregar su pedido</li>
                        <li><strong>Interés legítimo:</strong> Para mejorar nuestros servicios y prevenir fraude</li>
                        <li><strong>Consentimiento:</strong> Para comunicaciones de marketing</li>
                        <li><strong>Obligación legal:</strong> Para cumplir con requisitos fiscales (SUNAT)</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">5. Compartir Información con Terceros</h2>
                    <p class="mb-4">
                        Podemos compartir su información con:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li><strong>Proveedores de servicios de pago:</strong> Para procesar transacciones de forma segura</li>
                        <li><strong>Servicios de entrega:</strong> Solo la información necesaria para coordinar la entrega</li>
                        <li><strong>Autoridades:</strong> Cuando sea requerido por ley</li>
                    </ul>
                    <p class="mb-6">
                        <strong>No vendemos ni alquilamos su información personal a terceros para fines de marketing.</strong>
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">6. Seguridad de los Datos</h2>
                    <p class="mb-4">
                        Implementamos medidas de seguridad para proteger su información:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li>Conexión HTTPS segura (certificado SSL)</li>
                        <li>Almacenamiento seguro de datos</li>
                        <li>Acceso restringido solo a personal autorizado</li>
                        <li>No almacenamos información completa de tarjetas de crédito</li>
                    </ul>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">7. Retención de Datos</h2>
                    <p class="mb-6">
                        Conservamos su información personal durante el tiempo necesario para cumplir con los 
                        propósitos descritos en esta política, salvo que la ley requiera o permita un período 
                        de retención más prolongado. Los registros fiscales se mantienen según lo requerido 
                        por la SUNAT (mínimo 5 años).
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">8. Sus Derechos</h2>
                    <p class="mb-4">
                        Conforme a la Ley de Protección de Datos Personales del Perú (Ley N° 29733), usted tiene derecho a:
                    </p>
                    <ul class="list-disc pl-6 mb-6 space-y-2">
                        <li><strong>Acceso:</strong> Solicitar una copia de sus datos personales</li>
                        <li><strong>Rectificación:</strong> Corregir datos inexactos o incompletos</li>
                        <li><strong>Cancelación:</strong> Solicitar la eliminación de sus datos</li>
                        <li><strong>Oposición:</strong> Oponerse al procesamiento de sus datos para ciertos fines</li>
                        <li><strong>Portabilidad:</strong> Recibir sus datos en formato estructurado</li>
                        <li><strong>Revocación:</strong> Retirar su consentimiento en cualquier momento</li>
                    </ul>
                    <p class="mb-6">
                        Para ejercer estos derechos, contáctenos a través de WhatsApp: 
                        <a href="https://wa.me/51941492316" class="text-[var(--azul-primario)] hover:underline">941 492 316</a>
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">9. Cookies</h2>
                    <p class="mb-4">
                        Utilizamos cookies para:
                    </p>
                    <ul class="list-disc pl-6 mb-4 space-y-2">
                        <li>Mantener su sesión de compra (carrito)</li>
                        <li>Recordar sus preferencias</li>
                        <li>Analizar el tráfico del sitio</li>
                    </ul>
                    <p class="mb-6">
                        Puede configurar su navegador para rechazar cookies, pero esto puede afectar la 
                        funcionalidad del sitio.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">10. Enlaces a Sitios de Terceros</h2>
                    <p class="mb-6">
                        Nuestro sitio puede contener enlaces a sitios web de terceros. No somos responsables 
                        de las prácticas de privacidad de estos sitios. Le recomendamos revisar sus políticas 
                        de privacidad.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">11. Menores de Edad</h2>
                    <p class="mb-6">
                        Nuestros servicios no están dirigidos a menores de 18 años. No recopilamos 
                        intencionalmente información de menores sin el consentimiento de los padres o tutores.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">12. Cambios a esta Política</h2>
                    <p class="mb-6">
                        Podemos actualizar esta Política de Privacidad ocasionalmente. Los cambios significativos 
                        serán notificados a través de nuestro sitio web o por correo electrónico.
                    </p>

                    <h2 class="text-2xl font-bold text-[var(--enlaces-titulos)] mb-4">13. Contacto</h2>
                    <p class="mb-2">
                        Para preguntas sobre esta Política de Privacidad o el manejo de sus datos:
                    </p>
                    <ul class="list-none space-y-2 mb-6">
                        <li><strong>Responsable:</strong> Detallesymasflores SAC</li>
                        <li><strong>WhatsApp:</strong> <a href="https://wa.me/51941492316" class="text-[var(--azul-primario)] hover:underline">941 492 316</a></li>
                        <li><strong>Ubicación:</strong> Lima, Perú</li>
                    </ul>

                    <div class="mt-8 p-4 bg-blue-50 border-l-4 border-[var(--azul-primario)] rounded">
                        <p class="text-sm text-gray-700">
                            <strong>Su privacidad es importante para nosotros.</strong> 
                            Solo usamos su información para procesar su pedido y mejorar su experiencia. 
                            Nunca compartiremos sus datos con fines de marketing sin su consentimiento expreso.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
