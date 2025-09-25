<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\Action;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-box';
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestión Comercial';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Productos');
    }

    public static function getModelLabel(): string
    {
        return __('Producto');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Productos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // 1) Información Básica
            Section::make(__('Información Básica'))
                ->icon('iconoir-box')
                ->description(__('Datos principales del producto o servicio'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    // Campo empresa oculto - se asigna automáticamente
                    Hidden::make('company_id')
                        ->default(fn () => \App\Models\Company::where('is_active', true)->first()?->id),
                        
                    TextInput::make('code')
                        ->required()
                        ->maxLength(20)
                        ->label(__('Código del Producto'))
                        ->placeholder(__('Ej: PROD001'))
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),
                        
                    Select::make('product_type')
                        ->options([
                            'product' => __('Producto Físico'),
                            'service' => __('Servicio'),
                        ])
                        ->required()
                        ->native(false)
                        ->label(__('Tipo de Ítem'))
                        ->columnSpan(1),
                        
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->label(__('Nombre del Producto'))
                        ->placeholder(__('Nombre descriptivo del producto'))
                        ->columnSpan(2),
                        
                    TextInput::make('barcode')
                        ->maxLength(100)
                        ->label(__('Código de Barras'))
                        ->placeholder(__('EAN, UPC, etc.'))
                        ->columnSpan(1),
                        
                    Textarea::make('description')
                        ->maxLength(500)
                        ->label(__('Descripción'))
                        ->placeholder(__('Descripción detallada del producto'))
                        ->rows(3)
                        ->columnSpan(2),
                        
                    FileUpload::make('image_path')
                        ->label(__('Imagen del Producto'))
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                            '4:3',
                            '16:9',
                        ])
                        ->directory('products')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048) // 2MB
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText(__('Formatos permitidos: JPG, PNG, WEBP. Tamaño máximo: 2MB'))
                        ->columnSpan(1),
                ]),

            // 2) Clasificación y Unidades
            Section::make(__('Clasificación y Unidades'))
                ->icon('iconoir-label')
                ->description(__('Categorización y unidades de medida'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('category_id')
                        ->relationship('category', 'name', fn (Builder $query, callable $get) => 
                            $query->where('company_id', $get('company_id'))->where('status', true)
                        )
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(100)
                                ->label(__('Nombre de la Categoría')),
                            \Filament\Forms\Components\Hidden::make('company_id')
                                ->default(fn (callable $get) => $get('../../company_id'))
                                ->dehydrated(true),
                            \Filament\Forms\Components\Hidden::make('status')
                                ->default(true)
                                ->dehydrated(true),
                            \Filament\Forms\Components\Hidden::make('created_by')
                                ->default(fn () => auth()->id())
                                ->dehydrated(true),
                        ])
                        ->label(__('Categoría'))
                        ->placeholder(__('Seleccionar categoría'))
                        ->columnSpan(1),
                        
                    Select::make('brand_id')
                        ->relationship('brand', 'name', fn (Builder $query, callable $get) => 
                            $query->where('company_id', $get('company_id'))->where('status', true)
                        )
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            \Filament\Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(100)
                                ->label(__('Nombre de la Marca')),
                            \Filament\Forms\Components\Hidden::make('company_id')
                                ->default(fn (callable $get) => $get('../../company_id'))
                                ->dehydrated(true),
                            \Filament\Forms\Components\Hidden::make('status')
                                ->default(true)
                                ->dehydrated(true),
                            \Filament\Forms\Components\Hidden::make('created_by')
                                ->default(fn () => auth()->id())
                                ->dehydrated(true),
                        ])
                        ->label(__('Marca'))
                        ->placeholder(__('Seleccionar marca'))
                        ->columnSpan(1),
                        
                    Select::make('unit_code')
                        ->options([
                            'NIU' => __('NIU - Unidad (Bienes)'),
                            'ZZ' => __('ZZ - Servicio'),
                            'KGM' => __('KGM - Kilogramo'),
                            'MTR' => __('MTR - Metro'),
                            'LTR' => __('LTR - Litro'),
                            'M2' => __('M2 - Metro Cuadrado'),
                            'M3' => __('M3 - Metro Cúbico'),
                            'CEN' => __('CEN - Ciento'),
                            'MIL' => __('MIL - Millar'),
                            'DOZ' => __('DOZ - Docena'),
                        ])
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (callable $set, $state) {
                            $descriptions = [
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
                            $set('unit_description', $descriptions[$state] ?? 'UNIDAD (BIENES)');
                        })
                        ->label(__('Unidad de Medida'))
                        ->columnSpan(1),
                        
                    TextInput::make('unit_description')
                        ->maxLength(100)
                        ->default('UNIDAD (BIENES)')
                        ->label(__('Descripción de Unidad'))
                        ->placeholder(__('Descripción de la unidad'))
                        ->columnSpan(3),
                ]),

            // 3) Precios y Costos
            Section::make(__('Precios y Costos'))
                ->icon('iconoir-coins')
                ->description(__('Configuración de precios y costos'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('cost_price')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('S/')
                        ->label(__('Precio de Costo'))
                        ->placeholder(__('0.00'))
                        ->columnSpan(1),
                        
                    TextInput::make('unit_price')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->prefix('S/')
                        ->label(__('Precio Base'))
                        ->placeholder(__('0.00'))
                        ->columnSpan(1),
                        
                    TextInput::make('sale_price')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->prefix('S/')
                        ->label(__('Precio de Venta'))
                        ->placeholder(__('0.00'))
                        ->columnSpan(1),
                ]),

            // 4) Configuración Tributaria
            Section::make(__('Configuración Tributaria'))
                ->icon('iconoir-percentage')
                ->description(__('Configuración de impuestos y tributos'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('tax_type')
                        ->options([
                            '10' => __('10 - Gravado - Operación Onerosa'),
                            '20' => __('20 - Exonerado - Operación Onerosa'),
                            '30' => __('30 - Inafecto - Operación Onerosa'),
                        ])
                        ->required()
                        ->native(false)
                        ->label(__('Tipo de Afectación IGV'))
                        ->columnSpan(2),
                        
                    TextInput::make('tax_rate')
                        ->numeric()
                        ->step(0.01)
                        ->default(0.18)
                        ->suffix('%')
                        ->label(__('Tasa de IGV'))
                        ->placeholder(__('18.00'))
                        ->columnSpan(1),
                        
                    Toggle::make('taxable')
                        ->default(true)
                        ->label(__('Producto Gravable'))
                        ->helperText(__('Aplica IGV a este producto'))
                        ->columnSpan(1),
                ]),

            // 5) Inventario y Stock
            Section::make(__('Inventario y Stock'))
                ->icon('iconoir-package')
                ->description(__('Control de inventario y existencias por almacén'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Toggle::make('track_inventory')
                        ->default(true)
                        ->label(__('Controlar Inventario'))
                        ->helperText(__('Activar control de stock para este producto'))
                        ->live()
                        ->columnSpan(1),
                        
                    Select::make('warehouse_id')
                        ->label(__('Almacén'))
                        ->options(fn () => \App\Models\Warehouse::active()->forCompany(auth()->user()->company_id ?? 1)->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText(__('Selecciona el almacén donde se registrará el stock inicial'))
                        ->visible(fn (callable $get) => $get('track_inventory'))
                        ->columnSpan(2),
                        
                    TextInput::make('initial_stock')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->label(__('Stock Inicial'))
                        ->placeholder(__('0.00'))
                        ->helperText(__('Cantidad inicial en el almacén seleccionado'))
                        ->visible(fn (callable $get) => $get('track_inventory'))
                        ->columnSpan(1),
                        
                    TextInput::make('minimum_stock')
                        ->numeric()
                        ->step(0.01)
                        ->default(0)
                        ->label(__('Stock Mínimo'))
                        ->placeholder(__('0.00'))
                        ->helperText(__('Alerta cuando el stock sea menor a este valor'))
                        ->visible(fn (callable $get) => $get('track_inventory'))
                        ->columnSpan(1),
                ]),

            // 6) Estado y Configuración
            Section::make(__('Estado y Configuración'))
                ->icon('iconoir-settings')
                ->description(__('Estado del producto y configuraciones adicionales'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('status')
                        ->options([
                            'active' => __('Activo'),
                            'inactive' => __('Inactivo'),
                        ])
                        ->default('active')
                        ->native(false)
                        ->label(__('Estado del Producto'))
                        ->columnSpan(1),
                        
                    Toggle::make('for_sale')
                        ->default(true)
                        ->label(__('Disponible para Venta'))
                        ->helperText(__('Producto visible en ventas'))
                        ->columnSpan(1),
                ]),

            // Hidden audit fields
            Hidden::make('created_by')
                ->default(fn () => auth()->id())
                ->dehydrated(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->disk('public')
                    ->height(50)
                    ->width(50)
                    ->defaultImageUrl(url('/images/no-image.svg'))
                    ->circular()
                    ->action(
                        Action::make('view_image')
                            ->modalHeading(fn (Product $record): string => $record->name)
                            ->modalContent(fn (Product $record) => view('filament.modals.simple-image', [
                                'imageUrl' => $record->hasImage() ? \Storage::disk('public')->url($record->image_path) : null
                            ]))
                            ->modalWidth('xs')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel(__('Cerrar'))
                            ->visible(fn (Product $record): bool => $record->hasImage())
                    )
                    ->tooltip(fn (Product $record): string => 
                        $record->hasImage() ? __('Clic para ver imagen completa') : __('Sin imagen')
                    )
                    ->label(__('Imagen')),
                    
                TextColumn::make('code')
                    ->label(__('Código'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                    
                TextColumn::make('name')
                    ->label(__('Nombre'))
                    ->searchable(['name', 'description'])
                    ->sortable(),
                    
                TextColumn::make('product_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'product' => __('Producto'),
                        'service' => __('Servicio'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'info',
                        'service' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'product' => 'heroicon-m-cube',
                        'service' => 'heroicon-m-wrench-screwdriver',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->label(__('Tipo')),
                    
                TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Sin categoría'))
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ? 'gray' : 'gray')
                    ->label(__('Categoría')),
                    
                TextColumn::make('brand.name')
                    ->searchable()
                    ->placeholder(__('Sin marca'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Marca')),
                    
                TextColumn::make('unit_code')
                    ->badge()
                    ->color('success')
                    ->label(__('Unidad')),
                    
                TextColumn::make('sale_price')
                    ->money('PEN')
                    ->sortable()
                    ->label(__('Precio Venta')),
                    
                TextColumn::make('cost_price')
                    ->money('PEN')
                    ->placeholder(__('Sin costo'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Precio Costo')),
                    
                TextColumn::make('current_stock')
                    ->sortable()
                    ->color(function ($record): string {
                        if (!$record->track_inventory) return 'gray';
                        if ($record->current_stock <= $record->minimum_stock) return 'danger';
                        if ($record->current_stock <= ($record->minimum_stock * 1.5)) return 'warning';
                        return 'success';
                    })
                    ->formatStateUsing(function ($record): string {
                        if (!$record->track_inventory) {
                            return __('No controlado');
                        }
                        return number_format($record->current_stock, 2);
                    })
                    ->label(__('Stock')),
                    
                TextColumn::make('minimum_stock')
                    ->formatStateUsing(function ($record): string {
                        if (!$record->track_inventory) {
                            return __('N/A');
                        }
                        return number_format($record->minimum_stock, 2);
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Stock Mínimo')),
                    
                TextColumn::make('tax_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '10' => __('Gravado'),
                        '20' => __('Exonerado'),
                        '30' => __('Inafecto'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '10' => 'success',
                        '20' => 'warning',
                        '30' => 'gray',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('IGV')),
                    
                IconColumn::make('for_sale')
                    ->boolean()
                    ->trueIcon('heroicon-o-shopping-cart')
                    ->falseIcon('heroicon-o-no-symbol')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('En Venta')),
                    
                IconColumn::make('track_inventory')
                    ->boolean()
                    ->trueIcon('heroicon-o-archive-box')
                    ->falseIcon('heroicon-o-archive-box-x-mark')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Control Stock')),
                    
                TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('Activo'),
                        'inactive' => __('Inactivo'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->label(__('Estado')),
                    
                TextColumn::make('company.business_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Empresa')),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Creado')),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('product_type')
                    ->options([
                        'product' => __('Producto'),
                        'service' => __('Servicio'),
                    ])
                    ->label(__('Tipo de Ítem')),
                    
                SelectFilter::make('status')
                    ->options([
                        'active' => __('Activo'),
                        'inactive' => __('Inactivo'),
                    ])
                    ->label(__('Estado')),
                    
                SelectFilter::make('tax_type')
                    ->options([
                        '10' => __('Gravado (IGV)'),
                        '20' => __('Exonerado'),
                        '30' => __('Inafecto'),
                    ])
                    ->label(__('Tipo de IGV')),
                    
                TernaryFilter::make('for_sale')
                    ->label(__('Disponible para Venta'))
                    ->placeholder(__('Todos los productos'))
                    ->trueLabel(__('Solo en venta'))
                    ->falseLabel(__('No disponibles')),
                    
                TernaryFilter::make('track_inventory')
                    ->label(__('Control de Inventario'))
                    ->placeholder(__('Todos los productos'))
                    ->trueLabel(__('Con control de stock'))
                    ->falseLabel(__('Sin control de stock')),
                    
                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
                    
                Filter::make('low_stock')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('track_inventory', true)
                              ->whereColumn('current_stock', '<=', 'minimum_stock')
                    )
                    ->label(__('Stock bajo')),
                    
                SelectFilter::make('category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Categoría')),
                    
                SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Marca')),
                    
                Filter::make('no_category')
                    ->query(fn (Builder $query): Builder => $query->whereNull('category_id'))
                    ->label(__('Sin categoría')),
                    
                Filter::make('no_brand')
                    ->query(fn (Builder $query): Builder => $query->whereNull('brand_id'))
                    ->label(__('Sin marca')),
                    
                Filter::make('has_barcode')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('barcode'))
                    ->label(__('Con código de barras')),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])->label(__('Opciones')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
            'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}