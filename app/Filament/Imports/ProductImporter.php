<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Warehouse;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use App\Services\ProductTemplateService;
use Illuminate\Support\Facades\Log;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    // Almacena códigos ya procesados para detectar duplicados en el mismo archivo
    protected static array $processedCodes = [];

    public static function getColumns(): array
    {
        return [
            // Columnas requeridas - con mapeo español/inglés
            ImportColumn::make('code')
                ->label('Código')
                ->requiredMapping()
                ->guess(['code', 'codigo', 'Código *', 'Código', 'CÓDIGO', 'CODIGO'])
                ->rules(['required', 'max:50']),

            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->guess(['name', 'nombre', 'Nombre *', 'Nombre', 'NOMBRE'])
                ->rules(['required', 'max:500']),

            ImportColumn::make('price')
                ->label('Precio')
                ->numeric()
                ->guess(['price', 'precio', 'Precio *', 'Precio', 'PRECIO'])
                ->rules(['required', 'numeric', 'min:0']),

            ImportColumn::make('stock')
                ->label('Stock')
                ->numeric()
                ->guess(['stock', 'Stock *', 'Stock', 'STOCK'])
                ->rules(['required', 'numeric', 'min:0']),

            // Columnas opcionales - con mapeo español/inglés
            ImportColumn::make('category')
                ->label('Categoría')
                ->relationship(resolveUsing: 'name')
                ->guess(['category', 'categoria', 'Categoría', 'Categoria', 'CATEGORIA'])
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('brand')
                ->label('Marca')
                ->relationship(resolveUsing: 'name')
                ->guess(['brand', 'marca', 'Marca', 'MARCA'])
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('barcode')
                ->label('Código de Barras')
                ->guess(['barcode', 'codigo_barras', 'Código de Barras', 'codigo de barras', 'CODIGO DE BARRAS'])
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('description')
                ->label('Descripción')
                ->guess(['description', 'descripcion', 'Descripción', 'Descripcion', 'DESCRIPCION'])
                ->rules(['nullable', 'max:500']),

            ImportColumn::make('unit_code')
                ->label('Unidad de Medida')
                ->guess(['unit_code', 'unidad_medida', 'Unidad de Medida', 'unidad medida', 'UNIDAD DE MEDIDA'])
                ->rules(['nullable', 'in:NIU,ZZ,KGM,MTR,LTR,M2,M3,CEN,MIL,DOZ'])
                ->example('NIU'),

            ImportColumn::make('tax_type')
                ->label('Tipo de IGV')
                ->guess(['tax_type', 'tipo_igv', 'Tipo de IGV', 'tipo igv', 'TIPO DE IGV'])
                ->rules(['nullable', 'in:10,20,30'])
                ->example('10'),

            ImportColumn::make('cost_price')
                ->label('Precio de Costo')
                ->numeric()
                ->guess(['cost_price', 'precio_costo', 'Precio de Costo', 'precio costo', 'PRECIO DE COSTO'])
                ->rules(['nullable', 'numeric', 'min:0']),

            ImportColumn::make('sale_price')
                ->label('Precio de Venta')
                ->numeric()
                ->guess(['sale_price', 'precio_venta', 'Precio de Venta', 'precio venta', 'PRECIO DE VENTA'])
                ->rules(['nullable', 'numeric', 'min:0']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('warehouse_id')
                ->label('Almacén de Destino')
                ->options(function () {
                    $userCompanyId = auth()->user()->company_id ?? 2;
                    $options = Warehouse::where('company_id', $userCompanyId)
                        ->where('is_active', true)
                        ->pluck('name', 'id')
                        ->toArray();
                    if (empty($options)) {
                        $options = Warehouse::where('is_active', true)
                            ->pluck('name', 'id')
                            ->toArray();
                    }
                    if (empty($options)) {
                        $options = Warehouse::pluck('name', 'id')->toArray();
                    }
                    return $options;
                })
                ->default(1)
                ->required()
                ->helperText('Los productos se ingresarán a este almacén'),
        ];
    }

    public function resolveRecord(): ?Product
    {
        // Debug: Verificar qué datos estamos recibiendo
        Log::channel('carga')->info('=== INICIO PROCESAMIENTO PRODUCTO ===');
        Log::channel('carga')->info('Datos recibidos del archivo:', $this->data);
        
        if (!isset($this->data['code'])) {
            Log::channel('carga')->error('ERROR: No se encontró el campo code en los datos');
            return null;
        }
        
        $code = $this->data['code'];
        $companyId = auth()->user()->company_id ?? 2; // Fallback a company_id 2 si es null
        
        Log::channel('carga')->info('Procesando producto:', [
            'code' => $code,
            'company_id' => $companyId,
            'user_id' => auth()->id()
        ]);

        // Validar duplicados en el archivo actual
        $importKey = "{$companyId}_{$code}";
        if (isset(self::$processedCodes[$importKey])) {
            Log::channel('carga')->warning('Código duplicado detectado:', ['code' => $code]);
            $this->addValidationError('code', "Código duplicado en el archivo: {$code}");
            return null;
        }

        // Registrar código como procesado
        self::$processedCodes[$importKey] = true;

        // Buscar o crear producto
        $product = Product::firstOrNew([
            'company_id' => $companyId,
            'code' => $code,
        ]);
        
        Log::channel('carga')->info('Producto encontrado/creado:', [
            'id' => $product->id,
            'exists' => $product->exists,
            'code' => $product->code,
            'company_id' => $product->company_id
        ]);
        
        return $product;
    }

    protected function beforeSave(): void
    {
        Log::channel('carga')->info('=== BEFORE SAVE ===');
        Log::channel('carga')->info('Datos para guardar:', $this->data);
        Log::channel('carga')->info('Producto actual:', [
            'id' => $this->record->id,
            'code' => $this->record->code ?? 'nuevo',
            'exists' => $this->record->exists
        ]);
        
        $companyId = auth()->user()->company_id ?? 2; // Fallback a company_id 2 si es null

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
        Log::channel('carga')->info('=== AFTER SAVE ===');
        Log::channel('carga')->info('Producto guardado exitosamente:', [
            'id' => $this->record->id,
            'code' => $this->record->code,
            'name' => $this->record->name,
            'company_id' => $this->record->company_id
        ]);
        
        $companyId = auth()->user()->company_id ?? 2; // Fallback a company_id 2 si es null
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
        
        Log::channel('carga')->info('Proceso completado exitosamente:', [
            'producto_id' => $this->record->id,
            'stock_final' => $totalStock,
            'warehouse_id' => $warehouseId
        ]);
        Log::channel('carga')->info('=== FIN PROCESAMIENTO PRODUCTO ===\n');
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