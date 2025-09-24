<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentSeriesResource\Pages;
use App\Models\DocumentSeries;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
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
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class DocumentSeriesResource extends Resource
{
    protected static ?string $model = DocumentSeries::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-numbered-list-left';
    
    protected static string|UnitEnum|null $navigationGroup = 'Facturación';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'series';

    public static function getNavigationLabel(): string
    {
        return __('Series de Documentos');
    }

    public static function getModelLabel(): string
    {
        return __('Serie de Documento');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Series de Documentos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Información General'))
                    ->icon('heroicon-o-document-duplicate')
                    ->columns(2)
                    ->schema([
                    // Campo empresa oculto - se asigna automáticamente
                    \Filament\Forms\Components\Hidden::make('company_id'),
                    
                    Select::make('document_type')
                        ->options([
                            '01' => __('Factura'),
                            '03' => __('Boleta'),
                            '07' => __('Nota de Crédito'),
                            '08' => __('Nota de Débito'),
                            '09' => __('Nota de Venta (Uso Interno)'),
                            '12' => __('Ticket de Máquina Registradora'),
                            '13' => __('Documento emitido por bancos'),
                            '18' => __('Documentos de las iglesias y entidades religiosas'),
                            '31' => __('Guía de Remisión del Transportista'),
                            '40' => __('Comprobante de Percepción'),
                            '41' => __('Comprobante de Retención'),
                        ])
                        ->required()
                        ->native(false)
                        ->label(__('Tipo de Documento'))
                        ->columnSpan(1),
                    
                    TextInput::make('series')
                        ->required()
                        ->maxLength(4)
                        ->placeholder(__('Ej: F001, B001'))
                        ->label(__('Serie'))
                        ->columnSpan(1),
                    
                    TextInput::make('description')
                        ->required()
                        ->maxLength(200)
                        ->placeholder(__('Descripción de la serie'))
                        ->label(__('Descripción'))
                        ->columnSpan(1),
                ]),

                Section::make(__('Configuración de Numeración'))
                    ->icon('heroicon-o-hashtag')
                    ->columns(3)
                    ->schema([
                    
                    TextInput::make('current_number')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->label(__('Número Actual'))
                        ->columnSpan(1),
                    
                    TextInput::make('initial_number')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1)
                        ->label(__('Número Inicial'))
                        ->columnSpan(1),
                    
                    TextInput::make('final_number')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->label(__('Número Final'))
                        ->columnSpan(1),
                ]),

                Section::make(__('Configuración Adicional'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->columns(2)
                    ->schema([
                    
                    Toggle::make('is_default')
                        ->label(__('Es Predeterminado'))
                        ->columnSpan(1),
                    
                    Toggle::make('is_electronic')
                        ->label(__('Es Electrónico'))
                        ->default(true)
                        ->columnSpan(1),
                    
                    Select::make('status')
                        ->options([
                            'active' => __('Activo'),
                            'inactive' => __('Inactivo'),
                        ])
                        ->required()
                        ->native(false)
                        ->default('active')
                        ->label(__('Estado'))
                        ->columnSpan(1),
                    
                    DateTimePicker::make('last_used_at')
                        ->label(__('Último Uso'))
                        ->disabled()
                        ->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('series', 'asc')
            ->columns([
                TextColumn::make('company.business_name')
                    ->sortable()
                    ->searchable()
                    ->label(__('Empresa'))
                    ->weight('medium'),
                    
                TextColumn::make('document_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '01' => __('Factura'),
                        '03' => __('Boleta'),
                        '07' => __('Nota de Crédito'),
                        '08' => __('Nota de Débito'),
                        '09' => __('Nota de Venta (Uso Interno)'),
                        '12' => __('Ticket de Máquina Registradora'),
                        '13' => __('Documento emitido por bancos'),
                        '18' => __('Documentos de las iglesias y entidades religiosas'),
                        '31' => __('Guía de Remisión del Transportista'),
                        '40' => __('Comprobante de Percepción'),
                        '41' => __('Comprobante de Retención'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '01' => 'success',
                        '03' => 'info',
                        '07' => 'warning',
                        '08' => 'danger',
                        '09' => 'purple',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label(__('Tipo')),
                    
                TextColumn::make('series')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->label(__('Serie')),
                    
                TextColumn::make('description')
                    ->sortable()
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->label(__('Descripción')),
                    
                TextColumn::make('current_number')
                    ->sortable()
                    ->numeric()
                    ->label(__('Número Actual')),
                    
                IconColumn::make('is_default')
                    ->boolean()
                    ->sortable()
                    ->label(__('Predeterminado'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                IconColumn::make('is_electronic')
                    ->boolean()
                    ->sortable()
                    ->label(__('Electrónico'))
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('Activo'),
                        'inactive' => __('Inactivo'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->sortable()
                    ->label(__('Estado')),
                    
                TextColumn::make('last_used_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder(__('Nunca usado'))
                    ->label(__('Último Uso'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->options([
                        '01' => __('Factura'),
                        '03' => __('Boleta'),
                        '07' => __('Nota de Crédito'),
                        '08' => __('Nota de Débito'),
                        '09' => __('Nota de Venta (Uso Interno)'),
                        '12' => __('Ticket de Máquina Registradora'),
                    ])
                    ->label(__('Tipo de Documento')),
                    
                SelectFilter::make('status')
                    ->options([
                        'active' => __('Activo'),
                        'inactive' => __('Inactivo'),
                    ])
                    ->label(__('Estado')),
                    
                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
                    
                TernaryFilter::make('is_default')
                    ->label(__('Es Predeterminado'))
                    ->placeholder(__('Todos'))
                    ->trueLabel(__('Solo predeterminados'))
                    ->falseLabel(__('Solo no predeterminados')),
                    
                TernaryFilter::make('is_electronic')
                    ->label(__('Es Electrónico'))
                    ->placeholder(__('Todos'))
                    ->trueLabel(__('Solo electrónicos'))
                    ->falseLabel(__('Solo no electrónicos')),
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
                    DeleteBulkAction::make()
                        ->label(__('Eliminar seleccionados')),
                ])
                ->label(__('Acciones masivas')),
            ])
            ->emptyStateHeading(__('No hay series de documentos'))
            ->emptyStateDescription(__('Comience creando una nueva serie de documento.'))
            ->emptyStateIcon('heroicon-o-document-duplicate');
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
            'index' => Pages\ListDocumentSeries::route('/'),
            'create' => Pages\CreateDocumentSeries::route('/create'),
            'edit' => Pages\EditDocumentSeries::route('/{record}/edit'),
            'view' => Pages\ViewDocumentSeries::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('company')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}