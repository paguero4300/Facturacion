<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-building';
    
    protected static string|UnitEnum|null $navigationGroup = 'Inventario';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Almacenes');
    }

    public static function getModelLabel(): string
    {
        return __('Almacén');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Almacenes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('📦 Datos del Almacén'))
                    ->description(__('Información básica del almacén'))
                    ->icon('iconoir-building')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('company_id')
                                    ->label(__('Empresa'))
                                    ->relationship('company', 'business_name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                    
                                TextInput::make('code')
                                    ->label(__('Código'))
                                    ->required()
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('ALM001'),
                                    
                                TextInput::make('name')
                                    ->label(__('Nombre'))
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Almacén Principal')
                                    ->columnSpan(2),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_default')
                                    ->label(__('Almacén por Defecto'))
                                    ->helperText(__('Solo puede haber un almacén por defecto por empresa'))
                                    ->default(false),
                                    
                                Toggle::make('is_active')
                                    ->label(__('Activo'))
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.business_name')
                    ->label(__('Empresa'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('code')
                    ->label(__('Código'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('name')
                    ->label(__('Nombre'))
                    ->searchable()
                    ->sortable(),
                    
                IconColumn::make('is_default')
                    ->label(__('Por Defecto'))
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                    
                ToggleColumn::make('is_active')
                    ->label(__('Activo'))
                    ->sortable(),
                    
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
                    
                SelectFilter::make('is_active')
                    ->label(__('Estado'))
                    ->options([
                        1 => __('Activo'),
                        0 => __('Inactivo'),
                    ]),
                    
                SelectFilter::make('is_default')
                    ->label(__('Por Defecto'))
                    ->options([
                        1 => __('Sí'),
                        0 => __('No'),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWarehouses::route('/'),
            'create' => Pages\CreateWarehouse::route('/create'),
            'edit' => Pages\EditWarehouse::route('/{record}/edit'),
        ];
    }
}