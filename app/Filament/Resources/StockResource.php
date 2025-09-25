<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Models\Stock;
use App\Models\Company;
use App\Models\Product;
use App\Models\Warehouse;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use UnitEnum;
use BackedEnum;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-packages';
    
    protected static string|UnitEnum|null $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'product.name';

    public static function getNavigationLabel(): string
    {
        return __('Stocks');
    }

    public static function getModelLabel(): string
    {
        return __('Stock');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Stocks');
    }

    public static function canCreate(): bool
    {
        return false; // Solo lectura
    }

    public static function canEdit($record): bool
    {
        return false; // Solo lectura
    }

    public static function canDelete($record): bool
    {
        return false; // Solo lectura
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]); // No hay formulario
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product', 'warehouse', 'company']))
            ->columns([
                TextColumn::make('product.code')
                    ->label(__('Código'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('product.name')
                    ->label(__('Producto'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                    
                TextColumn::make('warehouse.name')
                    ->label(__('Almacén'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                    
                TextColumn::make('qty')
                    ->label(__('Cantidad'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($record) => $record->isLowStock() ? 'danger' : 'success'),
                    
                TextColumn::make('min_qty')
                    ->label(__('Mín. Stock'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),
                    
                BadgeColumn::make('stock_status')
                    ->label(__('Estado'))
                    ->getStateUsing(fn ($record) => $record->isLowStock() ? 'low' : 'ok')
                    ->colors([
                        'danger' => 'low',
                        'success' => 'ok',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'low' ? __('Stock Bajo') : __('OK')),
                    
                TextColumn::make('product.unit')
                    ->label(__('Unidad'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label(__('Actualizado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label(__('Almacén'))
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('product.category_id')
                    ->label(__('Categoría'))
                    ->relationship('product.category', 'name')
                    ->searchable()
                    ->preload(),
                    
                Filter::make('low_stock')
                    ->label(__('Stock Bajo'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('qty <= min_qty')),
                    
                Filter::make('zero_stock')
                    ->label(__('Sin Stock'))
                    ->query(fn (Builder $query): Builder => $query->where('qty', '<=', 0)),
                    
                Filter::make('positive_stock')
                    ->label(__('Con Stock'))
                    ->query(fn (Builder $query): Builder => $query->where('qty', '>', 0)),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('Ver'))
                    ->modalHeading(fn ($record) => __('Existencia: :product', ['product' => $record?->product?->name ?? 'Sin producto']))
                    ->modalContent(fn ($record) => $record ? view('filament.resources.stock.view-modal', compact('record')) : null),
            ])
            ->defaultSort('updated_at', 'desc')
            ->poll('30s') // Actualizar cada 30 segundos
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['company', 'product', 'warehouse', 'product.category'])
            ->whereHas('product', function (Builder $query) {
                $query->where('product_type', 'product');
            });
    }
}