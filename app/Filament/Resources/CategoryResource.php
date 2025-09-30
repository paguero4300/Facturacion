<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ColorPicker;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-label';

    protected static UnitEnum|string|null $navigationGroup = 'Gestión Comercial';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Categorías');
    }

    public static function getModelLabel(): string
    {
        return __('Categoría');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categorías');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Información de la Categoría'))
                ->icon('iconoir-label')
                ->description(__('Datos principales de la categoría de productos'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    // Campo empresa oculto - se asigna automáticamente
                    Hidden::make('company_id'),

                    Select::make('parent_id')
                        ->relationship('parent', 'name', fn (Builder $query) => $query->whereNull('parent_id')->where('status', true))
                        ->searchable()
                        ->preload()
                        ->label(__('Categoría Principal'))
                        ->placeholder(__('Seleccionar categoría principal (opcional)'))
                        ->helperText(__('Dejar vacío si es una categoría principal'))
                        ->columnSpan(1),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->label(__('Nombre de la Categoría'))
                        ->placeholder(__('Ej: Ocasiones, Arreglos, Peluches'))
                        ->unique(ignoreRecord: true)
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                        ->columnSpan(1),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(150)
                        ->label(__('Slug (URL)'))
                        ->placeholder(__('Ej: ocasiones, arreglos, peluches'))
                        ->helperText(__('Se genera automáticamente del nombre'))
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->label(__('Orden'))
                        ->helperText(__('Orden de aparición en el menú (menor primero)'))
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->maxLength(500)
                        ->label(__('Descripción'))
                        ->placeholder(__('Descripción detallada de la categoría'))
                        ->rows(3)
                        ->columnSpan(2),

                    FileUpload::make('image')
                        ->label(__('Imagen'))
                        ->image()
                        ->directory('categories')
                        ->maxSize(2048)
                        ->helperText(__('Imagen para mostrar en la web (máx. 2MB)'))
                        ->columnSpan(2),

                    ColorPicker::make('color')
                        ->label(__('Color de la Categoría'))
                        ->placeholder(__('#3B82F6'))
                        ->columnSpan(1),

                    TextInput::make('icon')
                        ->maxLength(50)
                        ->label(__('Icono'))
                        ->placeholder(__('heroicon-o-cube'))
                        ->helperText(__('Nombre del icono Heroicon'))
                        ->columnSpan(1),

                    Toggle::make('status')
                        ->default(true)
                        ->label(__('Categoría Activa'))
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
                TextColumn::make('parent.name')
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('Categoría Principal'))
                    ->badge()
                    ->color('primary')
                    ->label(__('Categoría Padre')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->label(__('Nombre')),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Slug copiado'))
                    ->label(__('Slug')),

                TextColumn::make('order')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->label(__('Orden')),

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

                ColorColumn::make('color')
                    ->placeholder(__('Sin color'))
                    ->label(__('Color')),

                TextColumn::make('icon')
                    ->placeholder(__('Sin icono'))
                    ->label(__('Icono')),

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
            ->defaultSort('order', 'asc')
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todas las categorías'))
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
            'view' => Pages\ViewCategory::route('/{record}'),
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