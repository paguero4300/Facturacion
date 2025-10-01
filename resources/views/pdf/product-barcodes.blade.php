<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3B82F6;
            padding-bottom: 15px;
        }
        
        .header h1 {
            color: #3B82F6;
            font-size: 18px;
            margin: 0;
            font-weight: bold;
        }
        
        .header .info {
            color: #6B7280;
            font-size: 10px;
            margin-top: 5px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background-color: #3B82F6;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #2563EB;
        }
        
        .table td {
            padding: 10px 8px;
            border: 1px solid #E5E7EB;
            font-size: 10px;
            vertical-align: top;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #F9FAFB;
        }
        
        .table tbody tr:hover {
            background-color: #EFF6FF;
        }
        
        .code-column {
            width: 25%;
            font-weight: bold;
            color: #1F2937;
        }
        
        .barcode-column {
            width: 30%;
            text-align: center;
            font-family: 'Courier New', monospace;
        }
        
        .name-column {
            width: 45%;
        }
        
        .no-barcode {
            color: #9CA3AF;
            font-style: italic;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6B7280;
            border-top: 1px solid #E5E7EB;
            padding-top: 15px;
        }
        
        .summary {
            background-color: #F3F4F6;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="info">
            Generado el: {{ $generated_at }} | Total de productos: {{ $total_products }}
        </div>
    </div>

    <div class="summary">
        <strong>Resumen:</strong> Este documento contiene los códigos de barras de todos los productos activos del sistema.
    </div>

    <table class="table">
        <thead>
            <tr>
                <th class="code-column">CODIGO PRODUCTO</th>
                <th class="barcode-column">CODIGO DE BARRAS</th>
                <th class="name-column">NOMBRE PRODUCTO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td class="code-column">{{ $product->code }}</td>
                    <td class="barcode-column">
                        @if($product->barcode)
                            {{ $product->barcode }}
                        @else
                            <span class="no-barcode">Sin código</span>
                        @endif
                    </td>
                    <td class="name-column">{{ $product->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: #9CA3AF;">
                        No hay productos disponibles
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>
            <strong>Sistema de Facturación</strong> | 
            Documento generado automáticamente | 
            {{ $products->count() }} productos listados
        </p>
    </div>
</body>
</html>