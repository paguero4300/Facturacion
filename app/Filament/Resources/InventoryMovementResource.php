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
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                Section::make(__('🚚 Datos del Movimiento'))
                    ->description(__('Información del movimiento de inventario'))
                    ->icon('iconoir-truck')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('company_id')
                                    ->label(__('Empresa'))
                                    ->relationship('company', 'business_name')
                                    ->required()
                                    ->default(function () {
                                        return \App\Models\Company::first()?->id;
                                    })
                                    ->disabled(),
                                    
                                Select::make('product_id')
                                    ->label(__('Producto'))
                                    ->relationship('product', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                Select::make('type')
                                    ->label(__('Tipo de Movimiento'))
                                    ->options(InventoryMovement::getTypes())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                        // Limpiar campos de almacén cuando cambia el tipo
                                        $set('from_warehouse_id', null);
                                        $set('to_warehouse_id', null);
                                    }),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Select::make('from_warehouse_id')
                                    ->label(__('Desde Almacén'))
                                    ->relationship('fromWarehouse', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => in_array($get('type'), [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER, InventoryMovement::TYPE_ADJUST]))
                                    ->required(fn (Get $get) => in_array($get('type'), [InventoryMovement::TYPE_OUT, InventoryMovement::TYPE_TRANSFER])),
                                    
                                Select::make('to_warehouse_id')
                                    ->label(__('Hacia Almacén'))
                                    ->relationship('toWarehouse', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (Get $get) => in_array($get('type'), [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER, InventoryMovement::TYPE_ADJUST]))
                                    ->required(fn (Get $get) => in_array($get('type'), [InventoryMovement::TYPE_OPENING, InventoryMovement::TYPE_IN, InventoryMovement::TYPE_TRANSFER])),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextInput::make('qty')
                                    ->label(__('Cantidad'))
                                    ->required()
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->placeholder('0.00'),
                                    
                                DateTimePicker::make('movement_date')
                                    ->label(__('Fecha del Movimiento'))
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                            ]),
                            
                        Textarea::make('reason')
                            ->label(__('Motivo/Observaciones'))
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder(__('Descripción del motivo del movimiento...')),
                            
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
                SelectFilter::make('company_id')
                    ->label(__('Empresa'))
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload(),
                    
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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