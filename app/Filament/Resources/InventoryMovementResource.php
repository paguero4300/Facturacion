<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
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
                // Sección 1: Información Básica
                Section::make(__('📋 Información Básica'))
                    ->description(__('Datos principales del movimiento'))
                    ->icon('iconoir-page')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('company_id')
                            ->default(function () {
                                return \App\Models\Company::where('is_active', true)->first()?->id ?? 1;
                            }),
                            
                        Placeholder::make('company_display')
                            ->label(__('Empresa'))
                            ->content(function () {
                                $company = \App\Models\Company::where('is_active', true)->first();
                                return $company ? $company->business_name : 'Empresa por defecto';
                            })
                            ->columnSpan(1),
                            
                        Select::make('product_id')
                            ->label(__('Producto'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                            
                        Select::make('type')
                            ->label(__('Tipo de Movimiento'))
                            ->options(InventoryMovement::getTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Limpiar campos de almacén cuando cambia el tipo
                                $set('from_warehouse_id', null);
                                $set('to_warehouse_id', null);
                                $set('adjust_type', null);
                            })
                            ->columnSpan(1),
                            
                        Select::make('adjust_type')
                            ->label(__('Tipo de Ajuste'))
                            ->options([
                                'positive' => __('Positivo (Entrada)'),
                                'negative' => __('Negativo (Salida)'),
                            ])
                            ->visible(fn (Get $get) => $get('type') === InventoryMovement::TYPE_ADJUST)
                            ->required(fn (Get $get) => $get('type') === InventoryMovement::TYPE_ADJUST)
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if ($get('type') === InventoryMovement::TYPE_ADJUST) {
                                    if ($state === 'positive') {
                                        // Ajuste positivo: solo to_warehouse_id
                                        $set('from_warehouse_id', null);
                                    } elseif ($state === 'negative') {
                                        // Ajuste negativo: solo from_warehouse_id
                                        $set('to_warehouse_id', null);
                                    }
                                }
                            })
                            ->columnSpan(1),
                    ]),

                // Sección 2: Detalles del Movimiento
                Section::make(__('🏪 Detalles del Movimiento'))
                    ->description(__('Almacenes, cantidad y fecha del movimiento'))
                    ->icon('iconoir-package')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('from_warehouse_id')
                            ->label(__('Desde Almacén'))
                            ->relationship('fromWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjust_type');
                                
                                // Mostrar para OUT, TRANSFER y ADJUST negativo
                                return in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'negative');
                            })
                            ->required(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjust_type');
                                
                                // Requerido para OUT, TRANSFER y ADJUST negativo
                                return in_array($type, [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'negative');
                            })
                            ->columnSpan(1),
                            
                        Select::make('to_warehouse_id')
                            ->label(__('Hacia Almacén'))
                            ->relationship('toWarehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjust_type');
                                
                                // Mostrar para OPENING, IN, TRANSFER y ADJUST positivo
                                return in_array($type, [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'positive');
                            })
                            ->required(function (Get $get) {
                                $type = $get('type');
                                $adjustType = $get('adjust_type');
                                
                                // Requerido para OPENING, IN, TRANSFER y ADJUST positivo
                                return in_array($type, [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER]) ||
                                       ($type === InventoryMovement::TYPE_ADJUST && $adjustType === 'positive');
                            })
                            ->columnSpan(1),
                            
                        TextInput::make('qty')
                            ->label(__('Cantidad'))
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(1),
                            
                        DateTimePicker::make('movement_date')
                            ->label(__('Fecha del Movimiento'))
                            ->required()
                            ->default(now())
                            ->native(false)
                            ->columnSpan(1),
                    ]),

                // Sección 3: Observaciones
                Section::make(__('📝 Observaciones'))
                    ->description(__('Motivo y comentarios adicionales'))
                    ->icon('iconoir-notes')
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('reason')
                            ->label(__('Motivo/Observaciones'))
                            ->maxLength(500)
                            ->rows(4)
                            ->placeholder(__('Descripción del motivo del movimiento...'))
                            ->columnSpanFull(),
                            
                        Hidden::make('user_id')
                            ->default(auth()->id()),
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