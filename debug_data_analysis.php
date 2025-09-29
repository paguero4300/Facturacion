<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=facturacion', 'root', '123456');
    echo '=== ANÁLISIS COMPLETO DE DATOS ===\n\n';

    // 1. Datos generales de todas las tablas
    echo '1. CONTEO GENERAL DE TODAS LAS TABLAS:\n';
    $tables = ['invoices', 'invoice_details', 'products', 'categories', 'companies', 'clients'];

    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "   $table: $count registros\n";
        } catch (Exception $e) {
            echo "   $table: Error - " . $e->getMessage() . "\n";
        }
    }

    // 2. Estados de invoices
    echo '\n2. ESTADOS DE INVOICES:\n';
    $stmt = $pdo->query('SELECT status, COUNT(*) as count FROM invoices GROUP BY status ORDER BY count DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '   ' . $row['status'] . ': ' . $row['count'] . ' registros\n';
    }

    // 3. Estados de SUNAT
    echo '\n3. ESTADOS SUNAT:\n';
    $stmt = $pdo->query('SELECT sunat_status, COUNT(*) as count FROM invoices GROUP BY sunat_status ORDER BY count DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '   ' . $row['sunat_status'] . ': ' . $row['count'] . ' registros\n';
    }

    // 4. Tipos de documento
    echo '\n4. TIPOS DE DOCUMENTO:\n';
    $stmt = $pdo->query('SELECT document_type, COUNT(*) as count FROM invoices GROUP BY document_type ORDER BY count DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '   ' . $row['document_type'] . ': ' . $row['count'] . ' registros\n';
    }

    // 5. Métodos de pago
    echo '\n5. MÉTODOS DE PAGO:\n';
    $stmt = $pdo->query('SELECT payment_method, COUNT(*) as count FROM invoices GROUP BY payment_method ORDER BY count DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '   ' . $row['payment_method'] . ': ' . $row['count'] . ' registros\n';
    }

    // 6. Fechas de invoices
    echo '\n6. RANGO DE FECHAS:\n';
    $stmt = $pdo->query('SELECT MIN(issue_date) as fecha_min, MAX(issue_date) as fecha_max FROM invoices');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '   Fecha mínima: ' . ($row['fecha_min'] ?? 'N/A') . '\n';
    echo '   Fecha máxima: ' . ($row['fecha_max'] ?? 'N/A') . '\n';

    // 7. Invoices con detalles
    echo '\n7. INVOICES CON/SIN DETALLES:\n';
    $sql = 'SELECT
        COUNT(DISTINCT i.id) as total_invoices,
        COUNT(DISTINCT id.invoice_id) as invoices_con_detalles,
        COUNT(DISTINCT i.id) - COUNT(DISTINCT id.invoice_id) as invoices_sin_detalles
    FROM invoices i
    LEFT JOIN invoice_details id ON i.id = id.invoice_id';

    $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    echo '   Total invoices: ' . $row['total_invoices'] . '\n';
    echo '   Con detalles: ' . $row['invoices_con_detalles'] . '\n';
    echo '   Sin detalles: ' . $row['invoices_sin_detalles'] . '\n';

    // 8. Muestra de datos recientes
    echo '\n8. MUESTRA DE INVOICES RECIENTES:\n';
    $stmt = $pdo->query('
        SELECT i.id, i.document_type, i.series, i.number, i.issue_date, i.status, i.sunat_status, i.payment_method, c.business_name
        FROM invoices i
        LEFT JOIN clients c ON i.client_id = c.id
        ORDER BY i.issue_date DESC
        LIMIT 5
    ');

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '   ID: ' . $row['id'] .
             ', Tipo: ' . $row['document_type'] .
             ', Serie-Número: ' . $row['series'] . '-' . $row['number'] .
             ', Fecha: ' . $row['issue_date'] .
             ', Estado: ' . $row['status'] .
             ', SUNAT: ' . $row['sunat_status'] .
             ', Pago: ' . $row['payment_method'] .
             ', Cliente: ' . ($row['business_name'] ?? 'N/A') . '\n';
    }

    // 9. Verificar si hay datos que SÍ deberían aparecer
    echo '\n9. INVOICES QUE DEBERÍAN APARECER (status pending o accepted):\n';
    $stmt = $pdo->query('SELECT * FROM invoices WHERE status IN ("pending", "accepted") LIMIT 3');

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($rows) > 0) {
        foreach ($rows as $row) {
            echo '   ENCONTRADO: ID ' . $row['id'] . ', Estado: ' . $row['status'] . ', Tipo: ' . $row['document_type'] . '\n';
        }
    } else {
        echo '   ❌ NO SE ENCONTRARON INVOICES CON STATUS "pending" O "accepted"\n';
    }

    // 10. Probar consulta exacta del SalesChannelResource
    echo '\n10. PRUEBA DE CONSULTA DEL SALESCHANNELRESOURCE:\n';
    $sql = 'SELECT COUNT(*) as count FROM invoice_details id
            INNER JOIN invoices i ON id.invoice_id = i.id
            WHERE i.status IN ("pending", "accepted")';

    $count = $pdo->query($sql)->fetch()['count'];
    echo '   Resultado consulta exacta: ' . $count . ' registros\n';

    echo '\n✅ Análisis completado\n';

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . '\n';
}