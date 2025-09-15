<?php

namespace App\Filament\Resources\InvoiceResource\Forms;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Actions\Action;
use App\Models\Product;
use App\Models\Company;
use App\Models\Category;
use App\Models\Brand;

class InvoiceDetailForm
{
    public static function make(): array
    {
        return [
            TextInput::make('line_number')
                ->numeric()
                ->label(__('Línea'))
                ->default(1)
                ->hidden()
                ->dehydrated(false),
                
            Select::make('product_id')
                ->relationship('product', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->label(__('Producto'))
                ->placeholder(__('Buscar producto...'))
                ->columnSpan(['default' => 12, 'md' => 4])
                ->reactive()
                ->createOptionForm([
                    TextInput::make('code')
                        ->label(__('Código'))
                        ->placeholder(__('PROD001'))
                        ->prefixIcon('heroicon-m-hashtag')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1)
                        ->rules([
                            function (callable $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    \Log::info('Validating product code in modal:', [
                                        'value' => $value
                                    ]);
                                    
                                    // Intentar obtener company_id de múltiples formas
                                    $companyId = $get('../../company_id') ?? $get('../company_id') ?? $get('company_id');
                                    
                                    \Log::info('Product modal validation context:', [
                                        'company_id' => $companyId,
                                        'product_code' => $value
                                    ]);
                                    
                                    if (!$companyId) {
                                        \Log::warning('Could not get company_id in product modal validation');
                                        return; // Skip validation if no company_id
                                    }
                                    
                                    if ($value) {
                                        // Usar consulta SQL directa para evitar problemas de cache
                                        $exists = \Illuminate\Support\Facades\DB::selectOne(
                                            'SELECT id FROM products WHERE company_id = ? AND code = ?',
                                            [$companyId, $value]
                                        );
                                        
                                        \Log::info('Product modal validation query result:', [
                                            'exists' => $exists ? 'YES' : 'NO',
                                            'product_id' => $exists?->id ?? 'null'
                                        ]);
                                        
                                        if ($exists) {
                                            $fail(__('Ya existe un producto con este código.'));
                                        }
                                    }
                                };
                            }
                        ]),
                        
                    TextInput::make('name')
                        ->label(__('Nombre'))
                        ->placeholder(__('Nombre del producto'))
                        ->prefixIcon('heroicon-m-cube')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                        
                    Select::make('product_type')
                        ->label(__('Tipo'))
                        ->prefixIcon('heroicon-m-squares-2x2')
                        ->options([
                            'product' => __('📦 Producto'),
                            'service' => __('🔧 Servicio'),
                        ])
                        ->default('product')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),
                        
                    TextInput::make('unit_code')
                        ->label(__('Unidad de Medida*'))
                        ->placeholder(__('NIU, KGM, etc.'))
                        ->prefixIcon('heroicon-m-scale')
                        ->default('NIU')
                        ->required()
                        ->maxLength(10)
                        ->columnSpan(1),
                        
                    Select::make('category_id')
                        ->label(__('Categoría'))
                        ->placeholder(__('Opcional'))
                        ->prefixIcon('heroicon-m-folder')
                        ->relationship('category', 'name', function ($query, callable $get) {
                            $companyId = $get('../../company_id');
                            if ($companyId) {
                                return $query->where('company_id', $companyId)->where('status', true);
                            }
                            return $query;
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->native(false)
                        ->columnSpan(1),
                        
                    Select::make('brand_id')
                        ->label(__('Marca'))
                        ->placeholder(__('Opcional'))
                        ->prefixIcon('heroicon-m-star')
                        ->relationship('brand', 'name', function ($query, callable $get) {
                            $companyId = $get('../../company_id');
                            if ($companyId) {
                                return $query->where('company_id', $companyId)->where('status', true);
                            }
                            return $query;
                        })
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->native(false)
                        ->columnSpan(1),
                        
                    TextInput::make('sale_price')
                        ->label(__('Precio'))
                        ->placeholder(__('0.00'))
                        ->prefixIcon('heroicon-m-currency-dollar')
                        ->prefix('S/ ')
                        ->numeric()
                        ->step(0.01)
                        ->minValue(0)
                        ->required()
                        ->columnSpan(1),
                        
                    Select::make('tax_type')
                        ->label(__('IGV'))
                        ->prefixIcon('heroicon-m-calculator')
                        ->options([
                            '10' => __('Gravado 18%'),
                            '20' => __('Exonerado'),
                            '30' => __('Inafecto'),
                        ])
                        ->default('10')
                        ->required()
                        ->native(false)
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->createOptionUsing(function (array $data, callable $get) {
                    $companyId = $get('../../company_id');
                    
                    \Log::info('Creating new product (validation should have prevented duplicates):', $data);
                    
                    $productData = [
                        'company_id' => $companyId,
                        'code' => $data['code'],
                        'name' => $data['name'],
                        'description' => '',
                        'category_id' => $data['category_id'] ?? null,
                        'brand_id' => $data['brand_id'] ?? null,
                        'product_type' => $data['product_type'],
                        'unit_code' => $data['unit_code'],
                        'unit_description' => 'UNIDAD',
                        'unit_price' => $data['sale_price'],
                        'sale_price' => $data['sale_price'],
                        'cost_price' => 0,
                        'tax_type' => $data['tax_type'],
                        'tax_rate' => $data['tax_type'] === '10' ? 0.18 : 0,
                        'current_stock' => 0,
                        'minimum_stock' => 0,
                        'track_inventory' => false,
                        'status' => 'active',
                        'taxable' => $data['tax_type'] === '10',
                        'for_sale' => true,
                        'created_by' => auth()->id(),
                    ];
                    
                    return Product::create($productData)->id;
                })
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading(__('Crear Producto'))
                        ->modalSubmitActionLabel(__('Crear'))
                        ->modalCancelActionLabel(__('Cancelar'))
                        ->modalWidth('lg')
                        ->modalIcon('heroicon-o-plus-circle')
                        ->color('success');
                })
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('description', $product->description);
                            $set('unit_price', $product->sale_price);
                            $set('unit_code', $product->unit_code);
                            $set('product_code', $product->code);
                            
                            // Calcular automáticamente
                            $quantity = $get('quantity') ?? 1;
                            $price = $product->sale_price;
                            $discount = $get('line_discount_amount') ?? 0;
                            
                            $gross = $quantity * $price;
                            $net = $gross - $discount;
                            
                            $set('gross_amount', $gross);
                            $set('net_amount', $net);
                            $set('igv_base_amount', $net);
                            $set('igv_amount', $net * 0.18);
                            $set('line_total', $net + ($net * 0.18));
                        }
                    }
                }),
                
            TextInput::make('description')
                ->label(__('Descripción'))
                ->hidden(),
                
            TextInput::make('product_code')
                ->label(__('Código'))
                ->hidden(),
                
            TextInput::make('quantity')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1)
                ->step(1)
                ->label(__('Cantidad*'))
                ->placeholder(__('1'))
                ->columnSpan(['default' => 12, 'md' => 2])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::calculateLine($state, $get, $set);
                }),
                
            TextInput::make('unit_price')
                ->numeric()
                ->step(0.01)
                ->required()
                ->minValue(0)
                ->label(__('Precio Unitario'))
                ->placeholder(__('0.00'))
                ->columnSpan(['default' => 12, 'md' => 2])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::calculateLine($state, $get, $set);
                }),
                
            TextInput::make('line_discount_amount')
                ->numeric()
                ->step(0.01)
                ->default(0)
                ->label(__('Descuento'))
                ->hidden()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::calculateLine($state, $get, $set);
                }),
                
            TextInput::make('gross_amount')
                ->numeric()
                ->step(0.01)
                ->label(__('Importe Bruto'))
                ->hidden(),
                
            TextInput::make('net_amount')
                ->numeric()
                ->step(0.01)
                ->label(__('Importe Neto'))
                ->hidden(),
                
            Select::make('tax_type')
                ->options([
                    '10' => __('Gravado - IGV 18%'),
                    '20' => __('Exonerado'),
                    '30' => __('Inafecto'),
                ])
                ->default('10')
                ->native(false)
                ->label(__('Tipo de Impuesto'))
                ->columnSpan(['default' => 12, 'md' => 2])
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    self::calculateLine(null, $get, $set);
                }),
                
            TextInput::make('igv_amount')
                ->numeric()
                ->step(0.01)
                ->label(__('IGV'))
                ->hidden(),
                
            TextInput::make('line_total')
                ->numeric()
                ->step(0.01)
                ->label(__('Total Línea'))
                ->columnSpan(['default' => 12, 'md' => 2])
                ->disabled()
                ->dehydrated(false),
                
            Toggle::make('is_free')
                ->label(__('Es gratuito'))
                ->default(false)
                ->hidden()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                    if ($state) {
                        $set('igv_amount', 0);
                        $set('line_total', 0);
                    } else {
                        self::calculateLine(null, $get, $set);
                    }
                }),
        ];
    }

    protected static function calculateLine($state, callable $get, callable $set): void
    {
        $quantity = $get('quantity') ?? 0;
        $price = $get('unit_price') ?? 0;
        $discount = $get('line_discount_amount') ?? 0;
        $taxType = $get('tax_type') ?? '10';
        $isFree = $get('is_free') ?? false;

        if ($isFree) {
            $set('gross_amount', 0);
            $set('net_amount', 0);
            $set('igv_amount', 0);
            $set('line_total', 0);
            return;
        }

        $gross = $quantity * $price;
        $net = $gross - $discount;
        
        $igvRate = 0.18;
        $igvAmount = 0;
        
        if ($taxType === '10') {
            $igvAmount = $net * $igvRate;
        }

        $set('gross_amount', $gross);
        $set('net_amount', $net);
        $set('igv_amount', $igvAmount);
        $set('line_total', $net + $igvAmount);
    }
}
