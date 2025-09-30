<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetallesResource\Pages;
use App\Models\Category;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\MoveAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class DetallesResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?string $navigationLabel = 'Detalles Web';

    protected static ?string $title = 'Gestión de Categorías Web';

    protected static ?string $slug = 'detalles';

    protected static ?int $navigationSort = 101;

    public static function getNavigationLabel(): string
    {
        return __('Detalles Web');
    }

    public static function getModelLabel(): string
    {
        return __('Categoría Web');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Categorías Web');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Información de la Categoría Web'))
                ->icon('heroicon-squares-2x2')
                ->description(__('Configuración de la categoría para la web'))
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    // Campo empresa oculto - se asigna automáticamente
                    Hidden::make('company_id'),

                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->label(__('Nombre de la Categoría'))
                        ->placeholder(__('Ej: Electrónicos, Ropa, Alimentos'))
                        ->unique(ignoreRecord: true)
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->maxLength(500)
                        ->label(__('Descripción'))
                        ->placeholder(__('Descripción detallada de la categoría'))
                        ->rows(3)
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
                        
                    Toggle::make('show_on_web')
                        ->default(true)
                        ->label(__('Mostrar en Web'))
                        ->helperText(__('Mostrar u ocultar esta categoría en la web'))
                        ->columnSpan(1),
                        
                    TextInput::make('web_order')
                        ->numeric()
                        ->default(0)
                        ->label(__('Orden en Web'))
                        ->helperText(__('Orden de visualización en la web (números más bajos aparecen primero)'))
                        ->columnSpan(1),
                        
                    TextInput::make('web_group')
                        ->maxLength(50)
                        ->label(__('Grupo Web'))
                        ->placeholder(__('Ej: principales, secundarias, especiales'))
                        ->helperText(__('Grupo al que pertenece esta categoría en la web'))
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

                ColorColumn::make('color')
                    ->placeholder(__('Sin color'))
                    ->label(__('Color')),

                TextColumn::make('icon')
                    ->placeholder(__('Sin icono'))
                    ->label(__('Icono')),
                    
                TextColumn::make('web_order')
                    ->sortable()
                    ->label(__('Orden Web')),
                    
                TextColumn::make('web_group')
                    ->sortable()
                    ->searchable()
                    ->label(__('Grupo Web')),

                IconColumn::make('show_on_web')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label(__('Visible en Web')),

                IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label(__('Estado')),

                TextColumn::make('products_count')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->label(__('Productos')),
            ])
            ->defaultSort('web_order', 'asc')
            ->reorderable('web_order')
            ->filters([
                TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todas las categorías'))
                    ->trueLabel(__('Solo activas'))
                    ->falseLabel(__('Solo inactivas')),
                    
                TernaryFilter::make('show_on_web')
                    ->label(__('Visibilidad Web'))
                    ->placeholder(__('Todas las categorías'))
                    ->trueLabel(__('Solo visibles'))
                    ->falseLabel(__('Solo ocultas')),

                SelectFilter::make('web_group')
                    ->options([
                        'principales' => 'Principales',
                        'secundarias' => 'Secundarias',
                        'especiales' => 'Especiales',
                    ])
                    ->label(__('Grupo Web')),

                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Bulk actions si son necesarias
                ]),
            ])
            ->emptyStateHeading(__('No hay categorías'))
            ->emptyStateDescription(__('Crea categorías para organizar tus productos en la web.'))
            ->emptyStateActions([
                \Filament\Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListDetalles::route('/'),
            'create' => Pages\CreateDetalles::route('/create'),
            'edit' => Pages\EditDetalles::route('/{record}/edit'),
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