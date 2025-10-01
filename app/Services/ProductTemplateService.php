<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductTemplateService
{
    /**
     * Generar archivo Excel de plantilla con formato profesional
     */
    public static function generateExcelTemplate()
    {
        // Verificar si Maatwebsite\Excel está disponible
        if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
            return self::generateRealExcelTemplate();
        }
        
        // Fallback a CSV si Excel no está disponible
        return self::generateCsvTemplate();
    }
    
    /**
     * Generar archivo Excel real usando Maatwebsite\Excel
     */
    private static function generateRealExcelTemplate()
    {
        try {
            $filename = 'plantilla-productos.xlsx';
            return \Maatwebsite\Excel\Facades\Excel::download(new ProductTemplateExport(), $filename);
        } catch (\Exception $e) {
            // Si falla, usar CSV como fallback
            return self::generateCsvTemplate();
        }
    }
    
    /**
     * Generar archivo CSV como fallback
     */
    private static function generateCsvTemplate(): StreamedResponse
    {
        $filename = 'plantilla-productos.csv';
        
        return new StreamedResponse(function() {
            $file = fopen('php://output', 'w');
            
            // Agregar BOM para UTF-8 (para que Excel reconozca caracteres especiales)
            fwrite($file, "\xEF\xBB\xBF");
            
            // Cabeceras en español
            fputcsv($file, [
                'Código *',
                'Nombre *',
                'Precio *',
                'Stock *',
                'Categoría',
                'Marca',
                'Código de Barras',
                'Descripción',
                'Unidad de Medida',
                'Tipo de IGV',
                'Precio de Costo',
                'Precio de Venta'
            ]);
            
            // Datos de ejemplo
            $exampleData = [
                ['PROD001', 'Laptop HP Pavilion 15', '2500.00', '10', 'Computadoras', 'HP', '7501234567890', 'Laptop para oficina con procesador Intel i5', 'NIU', '10', '2000.00', '2950.00'],
                ['SERV001', 'Consultoría IT', '100.00', '0', 'Servicios', '', '', 'Servicio de consultoría en tecnología', 'ZZ', '10', '80.00', '118.00'],
                ['PROD002', 'Mouse Inalámbrico', '50.00', '100', 'Accesorios', 'Logitech', '7501234567891', 'Mouse óptico inalámbrico', 'NIU', '10', '35.00', '59.00']
            ];
            
            foreach ($exampleData as $data) {
                fputcsv($file, $data);
            }
            
            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0'
        ]);
    }
}

// Clases para Excel (solo se usan si Maatwebsite\Excel está disponible)
class ProductTemplateExport implements \Maatwebsite\Excel\Concerns\WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Plantilla' => new ProductTemplateSheet(),
            'Instrucciones' => new InstructionsSheet(),
        ];
    }
}

class ProductTemplateSheet implements 
    \Maatwebsite\Excel\Concerns\FromArray, 
    \Maatwebsite\Excel\Concerns\WithHeadings, 
    \Maatwebsite\Excel\Concerns\WithColumnWidths, 
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles
{
    public function title(): string
    {
        return 'Plantilla';
    }

    public function headings(): array
    {
        // Cabeceras en español para mejor experiencia de usuario
        return [
            'Código *',
            'Nombre *',
            'Precio *',
            'Stock *',
            'Categoría',
            'Marca',
            'Código de Barras',
            'Descripción',
            'Unidad de Medida',
            'Tipo de IGV',
            'Precio de Costo',
            'Precio de Venta'
        ];
    }

    public function array(): array
    {
        return [
            ['PROD001', 'Laptop HP Pavilion 15', '2500.00', '10', 'Computadoras', 'HP', '7501234567890', 'Laptop para oficina con procesador Intel i5', 'NIU', '10', '2000.00', '2950.00'],
            ['SERV001', 'Consultoría IT', '100.00', '0', 'Servicios', '', '', 'Servicio de consultoría en tecnología', 'ZZ', '10', '80.00', '118.00'],
            ['PROD002', 'Mouse Inalámbrico', '50.00', '100', 'Accesorios', 'Logitech', '7501234567891', 'Mouse óptico inalámbrico', 'NIU', '10', '35.00', '59.00']
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Estilo para las cabeceras - Paleta Filament
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3B82F6'] // Azul Filament
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '1F2937'] // Gris oscuro Filament
                ]
            ]
        ]);

        // Estilo para los datos de ejemplo
        $sheet->getStyle('A2:L4')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'] // Gris claro Filament
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F9FAFB'] // Gris muy claro Filament
            ]
        ]);

        // Ajustar altura de la fila de cabeceras
        $sheet->getRowDimension(1)->setRowHeight(30);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Código
            'B' => 30, // Nombre
            'C' => 12, // Precio
            'D' => 10, // Stock
            'E' => 18, // Categoría
            'F' => 15, // Marca
            'G' => 20, // Código de Barras
            'H' => 35, // Descripción
            'I' => 20, // Unidad de Medida
            'J' => 15, // Tipo de IGV
            'K' => 18, // Precio de Costo
            'L' => 18  // Precio de Venta
        ];
    }
}

class InstructionsSheet implements 
    \Maatwebsite\Excel\Concerns\FromArray, 
    \Maatwebsite\Excel\Concerns\WithColumnWidths, 
    \Maatwebsite\Excel\Concerns\WithTitle,
    \Maatwebsite\Excel\Concerns\WithStyles
{
    public function title(): string
    {
        return 'Instrucciones';
    }

    public function array(): array
    {
        return [
            ['INSTRUCCIONES PARA IMPORTAR PRODUCTOS'],
            [''],
            ['FORMATO DE COLUMNAS (usar exactamente estos nombres):'],
            ['• Código *: Código único del producto (máx. 50 caracteres) - OBLIGATORIO'],
            ['• Nombre *: Nombre del producto (máx. 500 caracteres) - OBLIGATORIO'],
            ['• Precio *: Precio base del producto (número positivo) - OBLIGATORIO'],
            ['• Stock *: Cantidad inicial en inventario (número positivo) - OBLIGATORIO'],
            ['• Categoría: Categoría del producto (se crea automáticamente si no existe)'],
            ['• Marca: Marca del producto (se crea automáticamente si no existe)'],
            ['• Código de Barras: Código de barras del producto'],
            ['• Descripción: Descripción detallada del producto'],
            ['• Unidad de Medida: NIU, ZZ, KGM, MTR, LTR, M2, M3, CEN, MIL, DOZ'],
            ['• Tipo de IGV: 10 (Gravado), 20 (Exonerado), 30 (Inafecto)'],
            ['• Precio de Costo: Se calcula automáticamente si no se especifica'],
            ['• Precio de Venta: Se calcula automáticamente si no se especifica'],
            [''],
            ['NOTAS IMPORTANTES:'],
            ['• Los códigos deben ser únicos por empresa'],
            ['• Use punto (.) como separador decimal para precios'],
            ['• Las categorías y marcas se crearán automáticamente'],
            ['• El stock se asignará al almacén seleccionado en la importación'],
            ['• NO modifique los nombres de las columnas (cabeceras)'],
            ['• Puede eliminar las filas de ejemplo antes de agregar sus productos'],
            ['• Los campos marcados con * son obligatorios'],
            [''],
            ['UNIDADES DE MEDIDA VÁLIDAS:'],
            ['• NIU: Unidad (Bienes) - Por defecto'],
            ['• ZZ: Servicio'],
            ['• KGM: Kilogramo'],
            ['• MTR: Metro'],
            ['• LTR: Litro'],
            ['• M2: Metro Cuadrado'],
            ['• M3: Metro Cúbico'],
            ['• CEN: Ciento'],
            ['• MIL: Millar'],
            ['• DOZ: Docena'],
            [''],
            ['TIPOS DE IGV:'],
            ['• 10: Gravado - Operación Onerosa (18% IGV) - Por defecto'],
            ['• 20: Exonerado - Operación Onerosa (0% IGV)'],
            ['• 30: Inafecto - Operación Onerosa (0% IGV)'],
            [''],
            ['CÁLCULOS AUTOMÁTICOS:'],
            ['• Si no especifica Precio de Costo: se calcula como Precio * 0.70'],
            ['• Si no especifica Precio de Venta: se calcula como Precio * 1.18 (para productos gravados)'],
            ['• El stock mínimo se establece automáticamente en 5 unidades'],
            [''],
            ['EJEMPLOS DE DATOS VÁLIDOS:'],
            ['• Código: PROD001, SERV001, ACC001'],
            ['• Precio: 100.50, 2500.00, 15.99'],
            ['• Unidad de Medida: NIU, ZZ, KGM'],
            ['• Tipo de IGV: 10, 20, 30']
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Estilo para el título principal
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1F2937'] // Gris oscuro Filament
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'EFF6FF'] // Azul muy claro Filament
            ]
        ]);

        // Estilo para subtítulos
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6'] // Azul Filament
            ]
        ]);

        $sheet->getStyle('A17')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6']
            ]
        ]);

        $sheet->getStyle('A26')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6']
            ]
        ]);

        $sheet->getStyle('A38')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6']
            ]
        ]);

        $sheet->getStyle('A44')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6']
            ]
        ]);

        $sheet->getStyle('A49')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
                'color' => ['rgb' => '3B82F6']
            ]
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 85
        ];
    }
}