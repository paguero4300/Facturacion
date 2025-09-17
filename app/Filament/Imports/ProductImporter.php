<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use EightyNine\ExcelImport\EnhancedDefaultImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImporter extends EnhancedDefaultImport
{
    protected array $data = [];
    public function __construct(?string $model = null, array $attributes = [], array $additionalData = [])
    {
        parent::__construct($model ?: Product::class, $attributes);
        if (!empty($additionalData)) {
            $this->setAdditionalData($additionalData);
        }
    }

    public function getColumns(): array
    {
        return [
            'empresa' => [
                'label' => 'Empresa',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:100'],
                'cast' => 'string',
                'aliases' => ['Empresa', 'EMPRESA', 'company', 'Company', 'COMPANY', 'ruc', 'RUC', 'company_ruc', 'Company_RUC'],
            ],
            'codigo' => [
                'label' => 'Código',
                'required' => true,
                'rules' => ['required', 'string', 'max:50'],
                'cast' => 'string',
                'aliases' => ['Codigo', 'CODIGO', 'code', 'Code', 'CODE'],
            ],
            'nombre' => [
                'label' => 'Nombre',
                'required' => true,
                'rules' => ['required', 'string', 'max:255'],
                'cast' => 'string',
                'aliases' => ['Nombre', 'NOMBRE', 'name', 'Name', 'Descripción', 'DESCRIPCION'],
            ],
            'descripcion' => [
                'label' => 'Descripción',
                'required' => false,
                'rules' => ['nullable', 'string'],
                'cast' => 'string',
            ],
            'modelo' => [
                'label' => 'Modelo',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:100'],
                'cast' => 'string',
            ],
            'unidad_de_medida' => [
                'label' => 'Unidad de medida',
                'required' => true,
                'rules' => ['required', 'string', 'max:50'],
                'cast' => 'string',
                'aliases' => [
                    'Unidad de Medida', 'UNIDAD_DE_MEDIDA', 'unidad_medida', 'Unidad_Medida',
                    'unidad', 'Unidad', 'UNIDAD', 'descripcion_unidad', 'Descripcion_Unidad',
                    'DESCRIPCION_UNIDAD', 'unit_description', 'Unit_Description'
                ],
            ],
            'posee_igv' => [
                'label' => 'Posee IGV',
                'required' => false,
                'rules' => ['nullable', 'in:SI,NO,si,no,Si,No,10,20'],
                'cast' => 'string',
                'aliases' => [
                     'Posee IGV', 'POSEE_IGV', 'posee_igv', 'tiene_igv', 'Tiene IGV', 'TIENE_IGV',
                     'gravado', 'Gravado', 'GRAVADO', 'tipo_igv', 'Tipo_IGV', 'TIPO_IGV',
                     'tax_type', 'Tax_Type', 'TAX_TYPE'
                 ],
             ],
            'categoria' => [
                'label' => 'Categoría',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:100'],
                'cast' => 'string',
            ],
            'marca' => [
                'label' => 'Marca',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:100'],
                'cast' => 'string',
            ],
            'precio' => [
                'label' => 'Precio',
                'required' => true,
                'rules' => ['required', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
                'aliases' => ['Precio', 'PRECIO', 'precio_venta', 'Precio Venta', 'PRECIO_VENTA'],
            ],
            'fecha_de_vencimiento' => [
                'label' => 'Fecha de vencimiento',
                'required' => false,
                'rules' => ['nullable', 'date'],
                'cast' => 'date',
            ],
            'precio_unidad_1' => [
                'label' => 'Precio Unidad 1',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
            ],
            'descripcion_unidad_1' => [
                'label' => 'Descripción Unidad 1',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:50'],
                'cast' => 'string',
            ],
            'factor_unidad_1' => [
                'label' => 'Factor Unidad 1',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:4',
            ],
            'precio_costo_unidad_1' => [
                'label' => 'Precio Costo Unidad 1',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
            ],
            'precio_unidad_2' => [
                'label' => 'Precio Unidad 2',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
            ],
            'descripcion_unidad_2' => [
                'label' => 'Descripción Unidad 2',
                'required' => false,
                'rules' => ['nullable', 'string', 'max:50'],
                'cast' => 'string',
            ],
            'factor_unidad_2' => [
                'label' => 'Factor Unidad 2',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:4',
            ],
            'precio_costo_unidad_2' => [
                'label' => 'Precio Costo Unidad 2',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
            ],
            'stock_actual' => [
                'label' => 'Stock actual',
                'required' => false,
                'rules' => ['nullable', 'numeric', 'min:0'],
                'cast' => 'decimal:2',
            ],
            'imagenes' => [
                'label' => 'Imágenes',
                'required' => false,
                'rules' => ['nullable', 'string'],
                'cast' => 'string',
            ],
        ];
    }

    private function getCompanyId(?string $empresa = null): int
    {
        // Si se proporciona empresa, buscar por RUC o nombre comercial
        if (!empty($empresa)) {
            $company = \App\Models\Company::where('ruc', $empresa)
                ->orWhere('business_name', 'LIKE', '%' . $empresa . '%')
                ->orWhere('commercial_name', 'LIKE', '%' . $empresa . '%')
                ->first();
            
            if ($company) {
                return $company->id; // Retorna el ID de la empresa (1 o 2)
            }
        }
        
        // Fallback al usuario autenticado o company_id por defecto
        $userId = Auth::id();
        if ($userId) {
            $user = Auth::user();
            if ($user && $user->company_id) {
                return $user->company_id;
            }
        }
        
        // Si no hay usuario autenticado, usar company_id 1 por defecto
        return 1;
    }

    protected function beforeCreateRecord(array $data, $row): void
    {
        // Validar campos requeridos
        if (empty($data['codigo'] ?? null)) {
            $this->stopImportWithError("El campo 'codigo' es requerido y no puede estar vacío. Datos disponibles: " . implode(', ', array_keys($data)));
            return;
        }

        if (empty($data['nombre'] ?? null)) {
            $this->stopImportWithError("El campo 'nombre' es requerido y no puede estar vacío. Datos disponibles: " . implode(', ', array_keys($data)));
            return;
        }

        // Validar precio con más detalle - buscar en múltiples campos posibles
        $precioValue = $data['precio'] ?? 
                      $data['Precio'] ?? 
                      $data['PRECIO'] ?? 
                      $data['precio_venta'] ?? 
                      $data['Precio Venta'] ?? 
                      $data['PRECIO_VENTA'] ?? 
                      $data['precio_venta_1'] ?? 
                      null;
        
        if ($precioValue === null || $precioValue === '') {
            $this->stopImportWithError("El campo 'precio' es requerido y debe ser un número válido. Claves encontradas: " . implode(', ', array_keys($data)) . ". Valor encontrado: '" . ($precioValue ?? 'null') . "'");
            return;
        }

        // Validar que sea numérico
        if (!is_numeric($precioValue)) {
            $this->stopImportWithError("El campo 'precio' debe ser un número válido. Valor recibido: '" . $precioValue . "' (tipo: " . gettype($precioValue) . ")");
            return;
        }

        // Asignar el precio validado
        $data['precio'] = floatval($precioValue);

        // Validar unidad de medida con múltiples campos posibles
        $unidadMedida = $data['unidad_de_medida'] ?? 
                       $data['Unidad de Medida'] ?? 
                       $data['UNIDAD_DE_MEDIDA'] ?? 
                       $data['unidad_medida'] ?? 
                       $data['Unidad_Medida'] ?? 
                       $data['unidad'] ?? 
                       $data['Unidad'] ?? 
                       $data['UNIDAD'] ?? 
                       $data['descripcion_unidad'] ?? 
                       $data['Descripcion_Unidad'] ?? 
                       $data['DESCRIPCION_UNIDAD'] ?? 
                       null;
        
        if (empty($unidadMedida)) {
            $this->stopImportWithError("El campo 'unidad_de_medida' es requerido y no puede estar vacío. Claves encontradas: " . implode(', ', array_keys($data)) . ". Valor encontrado: '" . ($unidadMedida ?? 'null') . "'");
            return;
        }
        
        // Asignar la unidad de medida validada
        $data['unidad_de_medida'] = trim($unidadMedida);

        // Validar y convertir posee_igv (opcional)
        $poseeIgv = $data['posee_igv'] ?? 
                   $data['Posee IGV'] ?? 
                   $data['POSEE_IGV'] ?? 
                   $data['tiene_igv'] ?? 
                   $data['Tiene IGV'] ?? 
                   $data['TIENE_IGV'] ?? 
                   $data['gravado'] ?? 
                   $data['Gravado'] ?? 
                   $data['GRAVADO'] ?? 
                   $data['tipo_igv'] ?? 
                   $data['Tipo_IGV'] ?? 
                   $data['TIPO_IGV'] ?? 
                   null;
        
        // Si no se encuentra el campo, asignar valor por defecto
        if ($poseeIgv === null || $poseeIgv === '') {
            $data['posee_igv'] = 'SI'; // Valor por defecto
        } else {
            // Convertir códigos SUNAT a SI/NO
            if ($poseeIgv == '10' || strtoupper($poseeIgv) == 'SI' || strtoupper($poseeIgv) == 'YES' || $poseeIgv == '1') {
                $data['posee_igv'] = 'SI';
            } elseif ($poseeIgv == '20' || strtoupper($poseeIgv) == 'NO' || strtoupper($poseeIgv) == 'EXONERADO' || $poseeIgv == '0') {
                $data['posee_igv'] = 'NO';
            } else {
                $this->stopImportWithError("El campo 'posee_igv' debe ser SI, NO, 10 (gravado) o 20 (exonerado). Valor recibido: '" . $poseeIgv . "'");
                return;
            }
        }

        // Obtener company_id antes de validar código único
        $companyId = $this->getCompanyId($data['empresa'] ?? null);
        
        // Validar código único
        $existingProduct = Product::where('code', $data['codigo'])
            ->where('company_id', $companyId)
            ->first();

        if ($existingProduct) {
            $this->stopImportWithError("El código '{$data['codigo']}' ya existe en el sistema para la empresa ID {$companyId}.");
            return;
        }

        // Procesar categoría
        $categoryId = null;
        if (!empty($data['categoria'] ?? null)) {
            $category = Category::firstOrCreate(
                ['name' => $data['categoria'], 'company_id' => $companyId],
                ['description' => 'Categoría creada automáticamente durante importación']
            );
            $categoryId = $category->id;
        }

        // Procesar marca
        $brandId = null;
        if (!empty($data['marca'] ?? null)) {
            $brand = Brand::firstOrCreate(
                ['name' => $data['marca'], 'company_id' => $companyId],
                ['description' => 'Marca creada automáticamente durante importación']
            );
            $brandId = $brand->id;
        }

        // Procesar IGV
        $poseeIgvValue = $data['posee_igv'] ?? $data['posee igv'] ?? $data['tiene_igv'] ?? $data['tiene igv'] ?? null;
        if ($poseeIgvValue === null) {
            $this->stopImportWithError("El campo 'posee_igv' es requerido y debe ser SI o NO.");
            return;
        }
        $taxable = in_array(strtoupper($poseeIgvValue), ['SI', 'YES', '1', 'TRUE', 'VERDADERO']);
        $taxType = $taxable ? '10' : '20'; // Usar códigos SUNAT correctos
        $taxRate = $taxable ? 0.1800 : 0.0000;

        // Procesar imagen
        $imagePath = null;
        if (!empty($data['imagenes'] ?? null)) {
            $imagePath = $this->processImagePath($data['imagenes']);
        }

        // Limpiar el array y preparar datos para crear el producto
        $processedData = [
            'company_id' => $companyId,
            'code' => $data['codigo'],
            'name' => $data['nombre'],
            'description' => $data['descripcion'] ?? $data['descripción'] ?? '',
            'model' => $data['modelo'] ?? $data['model'] ?? $data['modelo'] ?? '',
            'unit_code' => 'NIU', // Código SUNAT por defecto para unidades
            'unit_description' => $data['unidad_de_medida'],
            'unit_price' => $data['precio_unidad_1'] ?? $data['precio_unidad 1'] ?? $precioValue,
            'sale_price' => $precioValue,
            'cost_price' => $data['precio_costo_unidad_1'] ?? $data['precio_costo_unidad 1'] ?? $data['precio costo unidad 1'] ?? 0,
            'tax_type' => $taxType,
            'tax_rate' => $taxRate,
            'taxable' => $taxable,
            'current_stock' => $data['stock_actual'] ?? $data['stock actual'] ?? $data['stock_actual'] ?? 0,
            'minimum_stock' => 0,
            'track_inventory' => true,
            'category_id' => $categoryId,
            'brand_id' => $brandId,
            'category' => $data['categoria'] ?? $data['categoría'] ?? null, // Para compatibilidad
            'brand' => $data['marca'] ?? null, // Para compatibilidad
            'image_path' => $imagePath,
            'created_by' => Auth::id(),
            'for_sale' => true,
            'status' => 'active',
        ];

        // Asignar los datos procesados a la propiedad de la clase
        $this->data = $processedData;
    }

    private function processImagePath(string $imagePath): ?string
    {
        // Si es una URL, intentar descargar la imagen
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return $this->downloadImageFromUrl($imagePath);
        }

        // Si es una ruta relativa, verificar si existe
        if (Storage::disk('public')->exists($imagePath)) {
            return $imagePath;
        }

        // Si no se puede procesar, retornar null
        return null;
    }

    private function downloadImageFromUrl(string $url): ?string
    {
        try {
            $contents = file_get_contents($url);
            if ($contents === false) {
                return null;
            }

            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $filename = 'products/' . Str::uuid() . '.' . $extension;
            
            Storage::disk('public')->put($filename, $contents);
            return $filename;
        } catch (\Exception $e) {
            // Log error but don't fail the import
            \Log::warning('Failed to download image from URL: ' . $url, ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function resolveRecord(): ?Product
    {
        // Crear el producto con los datos procesados
        return Product::create($this->data);
    }

    public function getValidationMessages(): array
    {
        return [
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique' => 'El código ya existe en el sistema.',
            'nombre.required' => 'El nombre es obligatorio.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número válido.',
            'posee_igv.in' => 'El campo Posee IGV debe ser SI o NO.',
        ];
    }
}