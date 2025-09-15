<?php

return [
    /*
    |--------------------------------------------------------------------------
    | QPse Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con QPse (Proveedor de Servicios 
    | Electrónicos) para facturación electrónica SUNAT.
    |
    */

    'mode' => env('QPSE_MODE', 'demo'),

    'url' => env('QPSE_URL', 'https://demo-cpe.qpse.pe'),

    'token' => env('QPSE_TOKEN'),

    'username' => env('QPSE_USERNAME'),

    'password' => env('QPSE_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | Información de la empresa que emite los comprobantes electrónicos
    |
    */

    'company' => [
        'ruc' => env('COMPANY_RUC', '20000000001'),
        'name' => env('COMPANY_NAME', 'MI EMPRESA SAC'),
        'address' => env('COMPANY_ADDRESS', 'Av. Ejemplo 123'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Configuraciones específicas de la integración
    |
    */

    'integration' => [
        // Usar QPse como PSE en lugar de conexión directa a SUNAT
        'use_pse' => env('QPSE_USE_PSE', true),
        
        // Timeout para peticiones HTTP
        'timeout' => env('QPSE_TIMEOUT', 30),
        
        // Logs habilitados
        'logs_enabled' => env('QPSE_LOGS_ENABLED', true),
        
        // Guardar XMLs para debug
        'save_xmls' => env('QPSE_SAVE_XMLS', false),
        
        // Directorio para XMLs (relativo a storage/app)
        'xmls_path' => 'qpse/xmls',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment URLs
    |--------------------------------------------------------------------------
    |
    | URLs para diferentes entornos
    |
    */

    'environments' => [
        'demo' => 'https://demo-cpe.qpse.pe',
        'production' => 'https://cpe.qpse.pe',
    ],

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    |
    | Tipos de documentos soportados y sus códigos
    |
    */

    'document_types' => [
        'invoice' => '01',      // Factura
        'credit' => '07',       // Nota de Crédito  
        'debit' => '08',        // Nota de Débito
        'despatch' => '09',     // Guía de Remisión
        'retention' => '20',    // Comprobante de Retención
        'perception' => '40',   // Comprobante de Percepción
    ],
];