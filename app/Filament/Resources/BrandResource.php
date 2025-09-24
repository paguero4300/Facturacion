<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-shop';

    protected static UnitEnum|string|null $navigationGroup = 'Gestión Comercial';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Marcas');
    }

    public static function getModelLabel(): string
    {
        return __('Marca');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Marcas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Información de la Marca'))
                ->icon('iconoir-shop')
                ->description(__('Datos principales de la marca de productos'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    // Campo empresa oculto - se asigna automáticamente
                    Hidden::make('company_id'),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->label(__('Nombre de la Marca'))
                        ->placeholder(__('Ej: Samsung, Nike, Coca-Cola'))
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->maxLength(500)
                        ->label(__('Descripción'))
                        ->placeholder(__('Descripción de la marca'))
                        ->rows(3)
                        ->columnSpan(2),

                    TextInput::make('logo_url')
                        ->url()
                        ->maxLength(255)
                        ->label(__('URL del Logo'))
                        ->placeholder(__('https://ejemplo.com/logo.png'))
                        ->columnSpan(1),

                    TextInput::make('website')
                        ->url()
                        ->maxLength(255)
                        ->label(__('Sitio Web'))
                        ->placeholder(__('https://www.marca.com'))
                        ->columnSpan(1),

                    Toggle::make('status')
                        ->default(true)
                        ->label(__('Marca Activa'))
                        ->helperText(__('Desactivar para ocultar en productos'))
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('Nombre')),

                TextColumn::make('description')
                    ->limit(50)
                    ->placeholder(__('Sin descripción'))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->label(__('Descripción')),

                TextColumn::make('website')
                    ->placeholder(__('Sin sitio web'))
                    ->url(fn ($record) => $record->website)
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-globe-alt')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Sitio Web')),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->label(__('Productos')),

                IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
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
            ->defaultSort('name', 'asc')
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todas las marcas'))
                    ->trueLabel(__('Solo activas'))
                    ->falseLabel(__('Solo inactivas')),

                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
            'view' => Pages\ViewBrand::route('/{record}'),
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