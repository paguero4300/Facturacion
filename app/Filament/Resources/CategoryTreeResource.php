<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryTreeResource\Pages;
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
use App\Filament\Resources\CategoryTreeResource\RelationManagers;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class CategoryTreeResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-queue-list';

    protected static UnitEnum|string|null $navigationGroup = 'Gestión Comercial';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('Categorías Principales');
    }

    public static function getModelLabel(): string
    {
        return __('Categoría Principal');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categorías Principales');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Categoría Principal'))
                ->icon('heroicon-o-folder-plus')
                ->description(__('Crear una nueva categoría principal del menú'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    Hidden::make('company_id'),
                    
                    Hidden::make('parent_id')
                        ->default(null),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->label(__('Nombre de la Categoría Principal'))
                        ->placeholder(__('Ej: Ocasiones, Arreglos, Regalos, Festivos'))
                        ->helperText(__('Esta será una categoría del menú principal'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                        ->columnSpan(1),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(150)
                        ->label(__('Slug (URL)'))
                        ->placeholder(__('Ej: ocasiones, peluches'))
                        ->helperText(__('Se genera automáticamente'))
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->label(__('Orden'))
                        ->helperText(__('Menor número aparece primero'))
                        ->columnSpan(1),

                    Toggle::make('status')
                        ->default(true)
                        ->label(__('Activo'))
                        ->helperText(__('Visible en el menú web'))
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->label(__('Descripción'))
                        ->placeholder(__('Descripción de la categoría'))
                        ->rows(2)
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
                        ->helperText(__('Desactivar para ocultar en el menú'))
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
                    ->weight('bold')
                    ->size('lg')
                    ->icon('heroicon-o-folder')
                    ->color('primary')
                    ->description(fn (Category $record): string => $record->description ?? 'Categoría Principal')
                    ->label(__('Categoría Principal')),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Copiado'))
                    ->icon('heroicon-o-link')
                    ->label(__('Slug')),

                TextColumn::make('order')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->label(__('Orden')),

                TextColumn::make('children_count')
                    ->counts('children')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-folder')
                    ->label(__('Subcategorías')),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-shopping-bag')
                    ->label(__('Productos')),

                IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label(__('Estado')),

                ColorColumn::make('color')
                    ->placeholder(__('Sin color'))
                    ->label(__('Color')),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todas'))
                    ->trueLabel(__('Solo activas'))
                    ->falseLabel(__('Solo inactivas')),

                SelectFilter::make('parent_id')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload()
                    ->label(__('Categoría Principal')),

                TernaryFilter::make('is_parent')
                    ->label(__('Tipo'))
                    ->placeholder(__('Todas'))
                    ->trueLabel(__('Solo principales'))
                    ->falseLabel(__('Solo subcategorías'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('parent_id'),
                        false: fn (Builder $query) => $query->whereNotNull('parent_id'),
                    ),
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
            RelationManagers\SubcategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoryTrees::route('/'),
            'create' => Pages\CreateCategoryTree::route('/create'),
            'edit' => Pages\EditCategoryTree::route('/{record}/edit'),
            'view' => Pages\ViewCategoryTree::route('/{record}'),
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
