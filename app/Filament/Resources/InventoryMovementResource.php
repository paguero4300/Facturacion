<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Stock;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use UnitEnum;
use BackedEnum;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-truck';
    
    protected static UnitEnum|string|null $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 3;
    
    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationLabel(): string
    {
        return __('Movimientos');
    }

    public static function getModelLabel(): string
    {
        return __('Movimiento');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Movimientos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Hidden company field
                Hidden::make('company_id')
                    ->default(function () {
                        return \App\Models\Company::where('is_active', true)->first()?->id ?? 1;
                    }),

                // Hidden user field
                Hidden::make('user_id')
                    ->default(auth()->id()),

                // Sección 1: Tipo de Movimiento
                Section::make(__('📋 Tipo de Movimiento'))
                    ->description(__('Seleccione el tipo de movimiento de inventario'))
                    ->icon('iconoir-page')
                    ->columns(1)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->label(__('Tipo de Movimiento'))
                            ->options(InventoryMovement::getTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Limpiar campos cuando cambia el tipo
                                $set('product_id', null);
                                $set('from_warehouse_id', null);
                                $set('to_warehouse_id', null);
                                $set('adjustment_type', null);
                                $set('qty', null);
                                $set('reason', null);
                            })
                            ->columnSpanFull(),
                    ]),

                // Sección 2: Detalles del Movimiento (Dinámica)
                Section::make(__('🏪 Detalles del Movimiento'))
                    ->description(__('Configure los detalles según el tipo de movimiento'))
                    ->icon('iconoir-package')
                    ->columns(2)
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => filled($get('type')))
                    ->schema([
                        // Producto - Siempre visible
                        Select::make('product_id')
                            ->label(__('Producto'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (Get $get) {
                                $type = $get('type');
                                $warehouseId = $get('from_warehouse_id');
                                
                                // Para salidas, solo mostrar productos con stock en el almacén seleccionado
                                if ($type === InventoryMovement::TYPE_OUT && $warehouseId) {
                                    return Product::whereHas('stocks', function ($query) use ($warehouseId) {
                                        $query->where('warehouse_id', $warehouseId)
                                              ->where('qty', '>', 0);
                                    })->pluck('name', 'id');
                                }
                                
                                return Product::pluck('name', 'id');
                            })
                            ->live()
                            ->columnSpan(2),

                        // Tipo de Ajuste - Solo para ADJUST
                        Select::make('adjustment_type')
                            ->label(__('Tipo de Ajuste'))
                            ->options([
                                'SHORTAGE' => __('Faltante'),
                                'SURPLUS' => __('Sobrante'),
                            ])
                            ->visible(fn (Get $get) => $get('type') === InventoryMovement::TYPE_ADJUST)
                            ->required(fn (Get $get) => $get('type') === InventoryMovement::TYPE_ADJUST)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($get('type') === InventoryMovement::TYPE_ADJUST) {
                                    if ($state === 'SHORTAGE') {
                                        // Faltante: solo from_warehouse_id
                                        $set('to_warehouse_id', null);
                                    } elseif ($state === 'SURPLUS') {
                                        // Sobrante: solo to_warehouse_id
                                        $set('from_warehouse_id', null);
                                    }
                                }
                            })
                            ->columnSpan(2),

                        // Almacén de Origen
                        Select::make('from_warehouse_id')
                            ->label(__('Almacén de Origen'))
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjustment_type');
                                
                                return in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'SHORTAGE');
                            })
                            ->required(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjustment_type');
                                
                                return in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'SHORTAGE');
                            })
                            ->live()
                            ->columnSpan(1),

                        // Almacén de Destino
                        Select::make('to_warehouse_id')
                            ->label(__('Almacén de Destino'))
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjustment_type');
                                
                                return in_array($type, [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'SURPLUS');
                            })
                            ->required(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjustment_type');
                                
                                return in_array($type, [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'SURPLUS');
                            })
                            ->columnSpan(1),

                        // Stock Disponible - Solo para salidas
                        Placeholder::make('available_stock')
                            ->label(__('Stock Disponible'))
                            ->content(function (Get $get) {
                                $productId = $get('product_id');
                                $warehouseId = $get('from_warehouse_id');
                                
                                if (!$productId || !$warehouseId) {
                                    return __('Seleccione producto y almacén');
                                }
                                
                                $stock = Stock::where('product_id', $productId)
                                            ->where('warehouse_id', $warehouseId)
                                            ->first();
                                
                                return $stock ? number_format($stock->qty, 2) : '0.00';
                            })
                            ->visible(function (Get $get) {
                                $type = $get('type');
                                return in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $get('adjustment_type') === 'SHORTAGE');
                            })
                            ->columnSpan(1),

                        // Cantidad
                        TextInput::make('qty')
                            ->label(__('Cantidad'))
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->rules(function (Get $get) {
                                $type = $get('type');
                                $productId = $get('product_id');
                                $warehouseId = $get('from_warehouse_id');
                                
                                // Validación de stock para salidas
                                if (in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                    ($type === InventoryMovement::TYPE_ADJUST && $get('adjustment_type') === 'SHORTAGE')) {
                                    
                                    if ($productId && $warehouseId) {
                                        $stock = Stock::where('product_id', $productId)
                                                    ->where('warehouse_id', $warehouseId)
                                                    ->first();
                                        
                                        $maxQty = $stock ? $stock->qty : 0;
                                        return ['max:' . $maxQty];
                                    }
                                }
                                
                                return [];
                            })
                            ->columnSpan(1),

                        // Fecha del Movimiento
                        DateTimePicker::make('movement_date')
                            ->label(__('Fecha del Movimiento'))
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->columnSpan(2),
                    ]),

                // Sección 3: Observaciones
                Section::make(__('📝 Observaciones'))
                    ->description(__('Motivo y comentarios adicionales'))
                    ->icon('iconoir-notes')
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => filled($get('type')))
                    ->schema([
                        Textarea::make('reason')
                            ->label(__('Motivo/Observaciones'))
                            ->maxLength(500)
                            ->rows(4)
                            ->placeholder(__('Descripción del motivo del movimiento...'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('ID'))
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('product.code')
                    ->label(__('Código'))
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('type')
                    ->label(__('Tipo'))
                    ->formatStateUsing(fn ($state) => InventoryMovement::getTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        InventoryMovement::TYPE_OPENING => 'success',
                        InventoryMovement::TYPE_IN => 'primary',
                        InventoryMovement::TYPE_OUT => 'warning',
                        InventoryMovement::TYPE_TRANSFER => 'info',
                        InventoryMovement::TYPE_ADJUST => 'secondary',
                        default => 'gray',
                    }),
                    
                TextColumn::make('product.name')
                    ->label(__('Producto'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
                TextColumn::make('warehouse_movement')
                    ->label(__('Movimiento'))
                    ->getStateUsing(fn ($record) => $record->getWarehouseMovementDescription())
                    ->badge()
                    ->color('gray'),
                    
                TextColumn::make('qty')
                    ->label(__('Cantidad'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),
                    
                TextColumn::make('reason')
                    ->label(__('Motivo'))
                    ->limit(30)
                    ->toggleable(),
                    
                TextColumn::make('movement_date')
                    ->label(__('Fecha'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                TextColumn::make('user.name')
                    ->label(__('Usuario'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('created_at')
                    ->label(__('Creado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('Tipo'))
                    ->options(InventoryMovement::getTypes()),
                    
                SelectFilter::make('product_id')
                    ->label(__('Producto'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('from_warehouse_id')
                    ->label(__('Desde Almacén'))
                    ->relationship('fromWarehouse', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('to_warehouse_id')
                    ->label(__('Hacia Almacén'))
                    ->relationship('toWarehouse', 'name')
                    ->searchable()
                    ->preload(),
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
            ])
            ->defaultSort('movement_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'view' => Pages\ViewInventoryMovement::route('/{record}'),
            'edit' => Pages\EditInventoryMovement::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['company', 'product', 'fromWarehouse', 'toWarehouse', 'user']);
    }
}