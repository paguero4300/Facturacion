<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    // Almacena códigos ya procesados para detectar duplicados en el mismo archivo
    protected static array $processedCodes = [];

    public static function getColumns(): array
    {
        return [
            // Columnas requeridas
            ImportColumn::make('code')
                ->label('Código')
                ->requiredMapping()
                ->rules(['required', 'max:50']),

            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'max:500']),

            ImportColumn::make('price')
                ->label('Precio')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('stock')
                ->label('Stock')
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),

            // Columnas opcionales
            ImportColumn::make('category')
                ->label('Categoría')
                ->relationship(resolveUsing: 'name')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('brand')
                ->label('Marca')
                ->relationship(resolveUsing: 'name')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('barcode')
                ->label('Código de Barras')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('description')
                ->label('Descripción')
                ->rules(['nullable', 'max:500']),

            ImportColumn::make('unit_code')
                ->label('Unidad de Medida')
                ->rules(['nullable', 'in:NIU,ZZ,KGM,MTR,LTR,M2,M3,CEN,MIL,DOZ'])
                ->example('NIU'),

            ImportColumn::make('tax_type')
                ->label('Tipo de IGV')
                ->rules(['nullable', 'in:10,20,30'])
                ->example('10'),

            ImportColumn::make('cost_price')
                ->label('Precio de Costo')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('sale_price')
                ->label('Precio de Venta')
                ->numeric()
                ->rules(['nullable', 'numeric', 'min:0']),
        ];
    }

    public function resolveRecord(): ?Product
    {
        $code = $this->data['code'];
        $companyId = auth()->user()->company_id;

        // Validar duplicados en el archivo actual
        $importKey = "{$companyId}_{$code}";
        if (isset(self::$processedCodes[$importKey])) {
            $this->addValidationError('code', "Código duplicado en el archivo: {$code}");
            return null;
        }

        // Registrar código como procesado
        self::$processedCodes[$importKey] = true;

        // Buscar o crear producto
        return Product::firstOrNew([
            'company_id' => $companyId,
            'code' => $code,
        ]);
    }

    protected function beforeSave(): void
    {
        $companyId = auth()->user()->company_id;

        // Mapeo de descripciones de unidades
        $unitDescriptions = [
            'NIU' => 'UNIDAD (BIENES)',
            'ZZ' => 'SERVICIO',
            'KGM' => 'KILOGRAMO',
            'MTR' => 'METRO',
            'LTR' => 'LITRO',
            'M2' => 'METRO CUADRADO',
            'M3' => 'METRO CÚBICO',
            'CEN' => 'CIENTO',
            'MIL' => 'MILLAR',
            'DOZ' => 'DOCENA',
        ];

        // Determinar unidad
        $unitCode = $this->data['unit_code'] ?? 'NIU';
        $unitDescription = $unitDescriptions[$unitCode] ?? 'UNIDAD (BIENES)';

        // Calcular precios si no se proporcionan
        $basePrice = (float) $this->data['price'];
        $costPrice = isset($this->data['cost_price']) && $this->data['cost_price'] !== null
            ? (float) $this->data['cost_price']
            : $basePrice * 0.70; // 30% de margen por defecto

        $salePrice = isset($this->data['sale_price']) && $this->data['sale_price'] !== null
            ? (float) $this->data['sale_price']
            : $basePrice * 1.18; // +18% IGV por defecto

        // Determinar tipo de IGV
        $taxType = $this->data['tax_type'] ?? '10'; // Gravado por defecto

        // Completar datos del producto
        $this->record->fill([
            'company_id' => $companyId,
            'name' => $this->data['name'],
            'description' => $this->data['description'] ?? null,
            'barcode' => $this->data['barcode'] ?? null,
            'unit_price' => $basePrice,
            'sale_price' => $salePrice,
            'cost_price' => $costPrice,
            'unit_code' => $unitCode,
            'unit_description' => $unitDescription,
            'product_type' => 'product',
            'tax_type' => $taxType,
            'tax_rate' => $taxType === '10' ? 0.1800 : 0.0000,
            'taxable' => $taxType === '10',
            'status' => 'active',
            'for_sale' => true,
            'track_inventory' => true,
            'minimum_stock' => 5,
            'created_by' => auth()->id(),
        ]);

        // Asignar categoría si se proporcionó
        if (isset($this->data['category']) && $this->data['category']) {
            $category = Category::firstOrCreate([
                'company_id' => $companyId,
                'name' => $this->data['category'],
            ], [
                'status' => true,
                'created_by' => auth()->id(),
            ]);
            $this->record->category_id = $category->id;
        }

        // Asignar marca si se proporcionó
        if (isset($this->data['brand']) && $this->data['brand']) {
            $brand = Brand::firstOrCreate([
                'company_id' => $companyId,
                'name' => $this->data['brand'],
            ], [
                'status' => true,
                'created_by' => auth()->id(),
            ]);
            $this->record->brand_id = $brand->id;
        }
    }

    protected function afterSave(): void
    {
        $companyId = auth()->user()->company_id;
        $warehouseId = $this->options['warehouse_id'] ?? 1;
        $qty = (float) $this->data['stock'];

        // 1. Verificar si el producto ya tiene stock en este almacén
        $existingStock = Stock::where([
            'company_id' => $companyId,
            'product_id' => $this->record->id,
            'warehouse_id' => $warehouseId,
        ])->first();

        if ($existingStock) {
            // Si ya existe, sumar la cantidad
            $existingStock->update([
                'qty' => $existingStock->qty + $qty,
                'min_qty' => 5,
            ]);
        } else {
            // Si no existe, crear nuevo registro
            Stock::create([
                'company_id' => $companyId,
                'product_id' => $this->record->id,
                'warehouse_id' => $warehouseId,
                'qty' => $qty,
                'min_qty' => 5,
            ]);
        }

        // 2. Registrar movimiento de inventario (INGRESO)
        InventoryMovement::create([
            'company_id' => $companyId,
            'product_id' => $this->record->id,
            'type' => InventoryMovement::TYPE_IN,
            'from_warehouse_id' => null, // Entrada externa
            'to_warehouse_id' => $warehouseId,
            'qty' => $qty,
            'reason' => 'import',
            'ref_type' => 'IMPORT',
            'ref_id' => $this->import->id,
            'user_id' => auth()->id(),
            'idempotency_key' => "import-{$this->import->id}-{$this->record->id}-" . now()->timestamp,
            'movement_date' => now(),
        ]);

        // 3. Calcular y actualizar current_stock desde la suma de todos los stocks
        $totalStock = Stock::where([
            'company_id' => $companyId,
            'product_id' => $this->record->id,
        ])->sum('qty');

        $this->record->update([
            'current_stock' => $totalStock
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        // Limpiar códigos procesados al finalizar
        self::$processedCodes = [];

        $body = 'Se han importado ' . number_format($import->successful_rows) . ' productos exitosamente.';

        if ($failureCount = $import->failed_rows) {
            $body .= ' ' . number_format($failureCount) . ' filas fallaron.';
        }

        return $body;
    }

    /**
     * Resetear códigos procesados al iniciar una nueva importación
     */
    public static function resetProcessedCodes(): void
    {
        self::$processedCodes = [];
    }
}