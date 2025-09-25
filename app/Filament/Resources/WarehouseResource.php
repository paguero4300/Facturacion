<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseResource\Pages;
use App\Models\Warehouse;
use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
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
                Section::make(__('Datos del Almacén'))
                    ->icon('iconoir-package')
                    ->description(__('Información básica del almacén'))
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        Hidden::make('company_id')
                            ->default(fn() => \App\Models\Company::where('status', 'active')->first()?->id ?? 1)
                            ->required(),
                        
                        TextInput::make('code')
                            ->label(__('Código'))
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(1),
                        
                        TextInput::make('name')
                            ->label(__('Nombre'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        
                        Toggle::make('is_default')
                            ->label(__('Almacén por Defecto'))
                            ->helperText(__('Solo puede haber un almacén por defecto por empresa'))
                            ->columnSpan(1),
                        
                        Toggle::make('is_active')
                            ->label(__('Activo'))
                            ->default(true)
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ])->label(__('Opciones')),
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