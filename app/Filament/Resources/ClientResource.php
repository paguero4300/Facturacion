<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Actions\Action;

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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-user';
    
    protected static string|UnitEnum|null $navigationGroup = 'Gestión Comercial';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'business_name';

    public static function getNavigationLabel(): string
    {
        return __('Clientes');
    }

    public static function getModelLabel(): string
    {
        return __('Cliente');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Clientes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // 1) Información Básica
            Section::make(__('Información Básica'))
                ->icon('iconoir-user')
                ->description(__('Datos principales del cliente'))
                ->columns(4)
                ->columnSpanFull()
                ->schema([
                    // Primera fila: Empresa (campo contextual importante)
                    Select::make('company_id')
                        ->relationship('company', 'business_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label(__('Empresa'))
                        ->placeholder(__('Seleccione la empresa'))
                        ->columnSpan(4),
                        
                    // Segunda fila: Identificación del cliente
                    Select::make('document_type')
                        ->options([
                            '6' => __('RUC - Registro Único de Contribuyentes'),
                            '1' => __('DNI - Documento Nacional de Identidad'),
                            '4' => __('Carnet de Extranjería'),
                            '7' => __('Pasaporte'),
                            'A' => __('Cédula Diplomática'),
                        ])
                        ->required()
                        ->native(false)
                        ->label(__('Tipo de Documento'))
                        ->placeholder(__('Seleccione tipo'))
                        ->columnSpan(1),
                        
                    TextInput::make('document_number')
                        ->required()
                        ->maxLength(15)
                        ->label(__('Número de Documento'))
                        ->placeholder(__('Ej: 20123456789'))
                        ->helperText(__('Complete el número para búsqueda automática'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set, $get) {
                            if (!$state) {
                                return;
                            }
                            
                            $documentType = $get('document_type');
                            
                            // Auto-buscar cuando se complete el número
                            if (($documentType === '1' && strlen($state) === 8) || 
                                ($documentType === '6' && strlen($state) === 11)) {
                                self::searchFactilizaData($state, $set, $get);
                            }
                        })
                        ->unique(
                            table: Client::class,
                            column: 'document_number',
                            modifyRuleUsing: function ($rule, $get) {
                                return $rule
                                    ->where('company_id', $get('company_id'))
                                    ->where('document_type', $get('document_type'));
                            }
                        )
                        ->validationMessages([
                            'unique' => __('Ya existe un cliente con este tipo y número de documento en la empresa seleccionada.')
                        ])
                        ->suffixAction(
                            Action::make('search_factiliza')
                                ->label(__('Buscar'))
                                ->icon('heroicon-m-magnifying-glass')
                                ->color('primary')
                                ->extraAttributes([
                                    'wire:loading.attr' => 'disabled',
                                    'wire:loading.class' => 'opacity-50 cursor-not-allowed',
                                    'wire:loading.class.remove' => 'hover:bg-primary-500',
                                    'wire:target' => 'callMountedAction',
                                ])
                                ->action(function ($get, $set) {
                                    $documentNumber = $get('document_number');
                                    $documentType = $get('document_type');
                                    
                                    if (!$documentNumber) {
                                        \Filament\Notifications\Notification::make()
                                            ->title(__('Campo requerido'))
                                            ->body(__('Ingrese el número de documento'))
                                            ->warning()
                                            ->send();
                                        return;
                                    }
                                    
                                    if (!$documentType) {
                                        \Filament\Notifications\Notification::make()
                                            ->title(__('Campo requerido'))
                                            ->body(__('Seleccione el tipo de documento'))
                                            ->warning()
                                            ->send();
                                        return;
                                    }
                                    
                                    self::searchFactilizaDataFromActions($documentNumber, $documentType, $set);
                                })
                        )
                        ->columnSpan(3),
                        
                    // Tercera fila: Nombres de la entidad
                    TextInput::make('business_name')
                        ->required()
                        ->maxLength(255)
                        ->label(__('Razón Social'))
                        ->placeholder(__('Nombre completo o razón social'))
                        ->helperText(__('Nombre legal o razón social oficial'))
                        ->columnSpan(3),
                        
                    TextInput::make('commercial_name')
                        ->maxLength(255)
                        ->label(__('Nombre Comercial'))
                        ->placeholder(__('Nombre comercial o marca'))
                        ->helperText(__('Nombre por el que se conoce comercialmente'))
                        ->columnSpan(1),
                ]),

            // 2) Información de Contacto
            Section::make(__('Información de Contacto'))
                ->icon('iconoir-phone')
                ->description(__('Datos de contacto y ubicación'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(20)
                        ->label(__('Teléfono'))
                        ->placeholder(__('Ej: +51 999 888 777'))
                        ->columnSpan(1),
                        
                    TextInput::make('email')
                        ->email()
                        ->maxLength(100)
                        ->label(__('Correo Electrónico'))
                        ->placeholder(__('cliente@empresa.com'))
                        ->columnSpan(1),
                        
                    TextInput::make('contact_person')
                        ->maxLength(100)
                        ->label(__('Persona de Contacto'))
                        ->placeholder(__('Nombre del contacto principal'))
                        ->columnSpan(1),
                        
                    TextInput::make('address')
                        ->maxLength(255)
                        ->label(__('Dirección'))
                        ->placeholder(__('Dirección completa'))
                        ->columnSpan(3),
                        
                    TextInput::make('district')
                        ->maxLength(100)
                        ->label(__('Distrito'))
                        ->placeholder(__('Ej: San Isidro'))
                        ->columnSpan(1),
                        
                    TextInput::make('province')
                        ->maxLength(100)
                        ->label(__('Provincia'))
                        ->placeholder(__('Ej: Lima'))
                        ->columnSpan(1),
                        
                    TextInput::make('department')
                        ->maxLength(100)
                        ->label(__('Departamento'))
                        ->placeholder(__('Ej: Lima'))
                        ->columnSpan(1),
                        
                    TextInput::make('ubigeo')
                        ->maxLength(10)
                        ->label(__('Código Ubigeo'))
                        ->placeholder(__('Ej: 150101'))
                        ->columnSpan(1),
                ]),

            // 3) Configuración Comercial
            Section::make(__('Configuración Comercial'))
                ->icon('iconoir-coins')
                ->description(__('Términos comerciales y de pago'))
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    Select::make('client_type')
                        ->options([
                            'regular' => __('Regular'),
                            'vip' => __('VIP'),
                            'wholesale' => __('Mayorista'),
                        ])
                        ->default('regular')
                        ->native(false)
                        ->label(__('Tipo de Cliente'))
                        ->columnSpan(1),
                        
                    TextInput::make('credit_limit')
                        ->numeric()
                        ->step(0.01)
                        ->prefix('S/')
                        ->default(0)
                        ->label(__('Límite de Crédito'))
                        ->placeholder(__('0.00'))
                        ->columnSpan(1),
                        
                    TextInput::make('payment_days')
                        ->numeric()
                        ->integer()
                        ->default(0)
                        ->suffix(__('días'))
                        ->label(__('Días de Pago'))
                        ->placeholder(__('30'))
                        ->columnSpan(1),
                        
                    Toggle::make('status')
                        ->default(true)
                        ->label(__('Cliente Activo'))
                        ->helperText(__('Desactivar para suspender operaciones con este cliente'))
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
                TextColumn::make('document_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '6' => 'RUC',
                        '1' => 'DNI',
                        '4' => 'CE',
                        '7' => 'PAS',
                        'A' => 'CD',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '6' => 'success',
                        '1' => 'info',
                        '4' => 'warning',
                        '7' => 'gray',
                        'A' => 'purple',
                        default => 'gray',
                    })
                    ->label(__('Tipo Doc.')),
                    
                TextColumn::make('document_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label(__('Documento')),
                    
                TextColumn::make('business_name')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->label(__('Razón Social')),
                    
                TextColumn::make('commercial_name')
                    ->searchable()
                    ->limit(25)
                    ->placeholder(__('Sin nombre comercial'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Nombre Comercial')),
                    
                TextColumn::make('client_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => __('Regular'),
                        'vip' => __('VIP'),
                        'wholesale' => __('Mayorista'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'gray',
                        'vip' => 'warning',
                        'wholesale' => 'success',
                        default => 'gray',
                    })
                    ->label(__('Tipo')),
                    
                TextColumn::make('phone')
                    ->icon('heroicon-m-phone')
                    ->placeholder(__('Sin teléfono'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Teléfono')),
                    
                TextColumn::make('email')
                    ->icon('heroicon-m-envelope')
                    ->placeholder(__('Sin email'))
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Email')),
                    
                TextColumn::make('credit_limit')
                    ->money('PEN')
                    ->placeholder(__('Sin límite'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Límite Crédito')),
                    
                TextColumn::make('payment_days')
                    ->suffix(' días')
                    ->placeholder(__('Sin plazo'))
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Días Pago')),
                    
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('document_type')
                    ->options([
                        '6' => __('RUC'),
                        '1' => __('DNI'),
                        '4' => __('Carnet de Extranjería'),
                        '7' => __('Pasaporte'),
                        'A' => __('Cédula Diplomática'),
                    ])
                    ->label(__('Tipo de Documento')),
                    
                SelectFilter::make('client_type')
                    ->options([
                        'regular' => __('Regular'),
                        'vip' => __('VIP'),
                        'wholesale' => __('Mayorista'),
                    ])
                    ->label(__('Tipo de Cliente')),
                    
                TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todos los estados'))
                    ->trueLabel(__('Solo activos'))
                    ->falseLabel(__('Solo inactivos')),
                    
                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
                    
                Filter::make('has_credit_limit')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('credit_limit')->where('credit_limit', '>', 0))
                    ->label(__('Con límite de crédito')),
                    
                Filter::make('has_contact_info')
                    ->query(fn (Builder $query): Builder => $query->where(function ($q) {
                        $q->whereNotNull('phone')->orWhereNotNull('email');
                    }))
                    ->label(__('Con información de contacto')),
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
            'view' => Pages\ViewClient::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * Buscar datos en Factiliza desde Actions
     */
    protected static function searchFactilizaDataFromActions($documentNumber, $documentType, $set): void
    {
        if (!$documentNumber) {
            return;
        }
        
        // Llamar al servicio de Factiliza
        $factilizaService = app(\App\Services\FactilizaService::class);
        
        if ($documentType === '1' && strlen($documentNumber) === 8) {
            // Buscar DNI
            $result = $factilizaService->consultarDni($documentNumber);
            
            if ($result['success'] && $result['data']) {
                $data = $result['data'];
                $set('business_name', $data['nombre_completo'] ?? '');
                $set('address', $data['direccion'] ?? '');
                $set('district', $data['distrito'] ?? '');
                $set('province', $data['provincia'] ?? '');
                $set('department', $data['departamento'] ?? '');
                $set('ubigeo', $data['ubigeo_sunat'] ?? '');
                
                \Filament\Notifications\Notification::make()
                    ->title(__('Datos encontrados'))
                    ->body(__('Datos encontrados'))
                    ->success()
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title(__('DNI no encontrado'))
                    ->body($result['message'] ?? __('No se encontraron datos para este DNI'))
                    ->warning()
                    ->send();
            }
        } elseif ($documentType === '6' && strlen($documentNumber) === 11) {
            // Buscar RUC
            $result = $factilizaService->consultarRuc($documentNumber);
            
            if ($result['success'] && $result['data']) {
                $data = $result['data'];
                $set('business_name', $data['nombre_o_razon_social'] ?? '');
                $set('address', $data['direccion'] ?? '');
                $set('district', $data['distrito'] ?? '');
                $set('province', $data['provincia'] ?? '');
                $set('department', $data['departamento'] ?? '');
                $set('ubigeo', $data['ubigeo_sunat'] ?? '');
                
                \Filament\Notifications\Notification::make()
                    ->title(__('Datos encontrados'))
                    ->body(__('Datos encontrados'))
                    ->success()
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title(__('RUC no encontrado'))
                    ->body($result['message'] ?? __('No se encontraron datos para este RUC'))
                    ->warning()
                    ->send();
            }
        } elseif ($documentType === '1' || $documentType === '6') {
            \Filament\Notifications\Notification::make()
                ->title(__('Formato inválido'))
                ->body(__('DNI debe tener 8 dígitos, RUC debe tener 11 dígitos'))
                ->danger()
                ->send();
        }
    }
    
    /**
     * Buscar datos en Factiliza (método original)
     */
    protected static function searchFactilizaData($documentNumber, $set, $get): void
    {
        $documentType = $get('document_type');
        self::searchFactilizaDataFromActions($documentNumber, $documentType, $set);
    }
}