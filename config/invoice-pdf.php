<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de PDFs de Facturas
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las opciones para la generación de PDFs
    | de facturas, boletas y otros comprobantes electrónicos.
    |
    */

    'format' => 'A4',
    
    'margins' => [
        'top' => 10,
        'right' => 10,
        'bottom' => 10,
        'left' => 10,
    ],

    'orientation' => 'portrait', // portrait o landscape

    'company' => [
        'logo_path' => 'images/logo.png', // Ruta relativa desde public/
        'show_logo' => true,
        'logo_width' => 150, // Ancho en píxeles
        'logo_height' => 60, // Alto en píxeles
    ],

    'document' => [
        'show_qr_code' => true,
        'show_footer' => true,
        'show_page_numbers' => false,
        'watermark' => null, // Texto de marca de agua (opcional)
    ],

    'styles' => [
        'font_family' => 'Arial, sans-serif',
        'font_size' => '12px',
        'line_height' => '1.4',
        'primary_color' => '#000000',
        'secondary_color' => '#666666',
        'border_color' => '#000000',
        'background_color' => '#ffffff',
    ],

    'table' => [
        'show_product_codes' => true,
        'show_unit_descriptions' => true,
        'show_discounts' => true,
        'min_rows' => 10, // Mínimo de filas a mostrar en la tabla
    ],

    'totals' => [
        'show_subtotal' => true,
        'show_igv' => true,
        'show_other_taxes' => false,
        'show_amount_in_words' => true,
    ],

    'payment_schedule' => [
        'show_when_credit' => true,
        'show_status' => true,
    ],

    'footer' => [
        'show_generation_date' => true,
        'show_sunat_status' => true,
        'custom_text' => 'Este documento ha sido generado electrónicamente y tiene validez legal.',
    ],

    'file_naming' => [
        'pattern' => '{document_type}_{series}_{number}', // Patrón para nombres de archivo
        'date_format' => 'Y-m-d_H-i-s', // Formato de fecha para archivos
    ],

    'storage' => [
        'disk' => 'public',
        'path' => 'invoices/pdfs',
        'keep_files' => false, // Mantener archivos después de la descarga
    ],
];