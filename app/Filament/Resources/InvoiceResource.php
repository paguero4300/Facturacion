<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\DocumentSeries;
use App\Models\Company;
use App\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkAction;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Coolsam\Flatpickr\Forms\Components\Flatpickr;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
// Tabs removed per request; using stacked Sections
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Schemas\Components\Fieldset;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;
use Filament\Support\Enums\FontWeight;
use function Spatie\LaravelPdf\Support\pdf;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-page';

    protected static UnitEnum|string|null $navigationGroup = 'Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_number';

    public static function getNavigationLabel(): string
    {
        return __('Comprobantes');
    }

    public static function getModelLabel(): string
    {
        return __('Comprobante');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Comprobantes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // 1) Datos Básicos
            Section::make(__('Datos Básicos'))
                ->icon('iconoir-page')
                ->columns(4)
                ->columnSpanFull()
                ->schema([
                    // Empresa y Cliente
                    Select::make('company_id')
                        ->relationship('company', 'business_name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->default(function () {
                            if (Company::query()->count() === 1) {
                                return Company::query()->value('id');
                            }
                            return null;
                        })
                        // ->hidden(fn () => Company::query()->count() === 1) // Comentado para mostrar siempre
                        ->label(__('Empresa'))
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // Auto-select document series when company changes
                            $docType = $get('document_type');
                            if ($state && $docType) {
                                $series = DocumentSeries::query()
                                    ->where('company_id', $state)
                                    ->where('document_type', $docType)
                                    ->active()
                                    ->orderBy('series')
                                    ->first();
                                
                                if ($series) {
                                    $set('document_series_id', $series->id);
                                    $set('series', $series->series);
                                    $set('number', str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT));
                                } else {
                                    $set('document_series_id', null);
                                    $set('series', null);
                                    $set('number', null);
                                }
                            }
                        })
                        ->columnSpan(2),

                    Select::make('client_id')
                        ->options(function (callable $get) {
                            $doc = $get('document_type');
                            $query = Client::query();
                            if ($doc === '01') { // Factura => RUC
                                $query->where('document_type', '6');
                            } elseif ($doc === '03') { // Boleta => DNI
                                $query->where('document_type', '1');
                            }
                            return $query->orderBy('business_name')->pluck('business_name', 'id')->all();
                        })
                        ->required()
                        ->searchable()
                        ->preload()
                        ->reactive()
                        ->label(__('Cliente'))
                        ->rule(function (callable $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                if (! $value) return;
                                $doc = $get('document_type');
                                $client = Client::find($value);
                                if (! $client) {
                                    $fail(__('Cliente inválido'));
                                    return;
                                }
                                if ($doc === '01' && $client->document_type !== '6') {
                                    $fail(__('Para Factura el cliente debe tener RUC.'));
                                }
                                if ($doc === '03' && $client->document_type !== '1') {
                                    $fail(__('Para Boleta el cliente debe tener DNI.'));
                                }
                            };
                        })
                        ->createOptionForm([
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
                                ->live(),
                                
                            TextInput::make('document_number')
                                ->required()
                                ->maxLength(15)
                                ->label(__('Número de Documento'))
                                ->placeholder(__('Ej: 20123456789'))
                                ->live(onBlur: true)
                                ->rules([
                                    function (callable $get) {
                                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                                            \Log::info('Validating document_number in modal:', [
                                                'value' => $value,
                                                'document_type' => $get('document_type')
                                            ]);
                                            
                                            // Intentar obtener company_id de múltiples formas
                                            $companyId = $get('../../company_id') ?? $get('../company_id') ?? $get('company_id');
                                            $documentType = $get('document_type');
                                            
                                            \Log::info('Modal validation context:', [
                                                'company_id' => $companyId,
                                                'document_type' => $documentType,
                                                'document_number' => $value
                                            ]);
                                            
                                            if (!$companyId) {
                                                \Log::warning('Could not get company_id in modal validation');
                                                return; // Skip validation if no company_id
                                            }
                                            
                                            if ($documentType && $value) {
                                                // Usar consulta SQL directa para evitar problemas de cache
                                                $exists = DB::selectOne(
                                                    'SELECT id FROM clients WHERE company_id = ? AND document_type = ? AND document_number = ?',
                                                    [$companyId, $documentType, $value]
                                                );
                                                
                                                \Log::info('Modal validation query result:', [
                                                    'exists' => $exists ? 'YES' : 'NO',
                                                    'client_id' => $exists?->id ?? 'null'
                                                ]);
                                                
                                                if ($exists) {
                                                    $fail(__('Ya existe un cliente con este tipo y número de documento.'));
                                                }
                                            }
                                        };
                                    }
                                ])
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if (!$state) {
                                        return;
                                    }
                                    
                                    $documentType = $get('document_type');
                                    
                                    // Auto-buscar cuando se complete el número
                                    if (($documentType === '1' && strlen($state) === 8) || 
                                        ($documentType === '6' && strlen($state) === 11)) {
                                        self::searchFactilizaDataForModal($state, $documentType, $set);
                                    }
                                })
                                ->suffixAction(
                                    Action::make('search_factiliza_modal')
                                        ->label(__('Buscar'))
                                        ->icon('heroicon-m-magnifying-glass')
                                        ->color('primary')
                                        ->extraAttributes([
                                            'wire:loading.attr' => 'disabled',
                                            'wire:loading.class' => 'opacity-50 cursor-not-allowed',
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
                                            
                                            self::searchFactilizaDataForModal($documentNumber, $documentType, $set);
                                        })
                                ),
                                
                            TextInput::make('business_name')
                                ->required()
                                ->maxLength(255)
                                ->label(__('Razón Social')),
                                
                            TextInput::make('commercial_name')
                                ->maxLength(255)
                                ->label(__('Nombre Comercial')),
                                
                            TextInput::make('address')
                                ->maxLength(255)
                                ->label(__('Dirección')),
                                
                            TextInput::make('email')
                                ->email()
                                ->maxLength(100)
                                ->label(__('Email')),
                                
                            TextInput::make('phone')
                                ->tel()
                                ->maxLength(20)
                                ->label(__('Teléfono')),
                        ])
                        ->createOptionUsing(function (array $data, callable $get) {
                            \Log::info('=== CLIENT CREATE OPTION USING START ===');
                            \Log::info('Raw data received:', $data);
                            
                            // Obtener empresa del formulario principal con múltiples intentos
                            $companyId = $get('company_id') ?? $get('../../company_id') ?? $get('../company_id');
                            
                            \Log::info('Company ID obtained:', ['company_id' => $companyId]);
                            
                            if (!$companyId) {
                                \Log::error('No company ID found in form context');
                                throw new \Exception('No se pudo obtener el ID de la empresa');
                            }
                            
                            // Normalizar tipos de datos
                            $documentType = (string) $data['document_type'];
                            $documentNumber = (string) $data['document_number'];
                            
                            \Log::info('Searching for existing client with:', [
                                'company_id' => $companyId,
                                'document_type' => $documentType,
                                'document_number' => $documentNumber
                            ]);
                            
                            \Log::info('Proceeding to create new client (validation should have prevented duplicates)');
                            
                            // Preparar datos para crear cliente
                            $clientData = [
                                'company_id' => $companyId,
                                'document_type' => $documentType,
                                'document_number' => $documentNumber,
                                'business_name' => $data['business_name'],
                                'commercial_name' => $data['commercial_name'] ?? null,
                                'address' => $data['address'] ?? null,
                                'email' => $data['email'] ?? null,
                                'phone' => $data['phone'] ?? null,
                                'status' => true,
                                'client_type' => 'regular',
                                'credit_limit' => 0,
                                'payment_days' => 0,
                                'created_by' => auth()->id(),
                            ];
                            
                            \Log::info('Creating client with final data:', $clientData);
                            
                            try {
                                $client = \App\Models\Client::create($clientData);
                                \Log::info('Client created successfully:', ['id' => $client->id]);
                                \Log::info('=== CLIENT CREATE OPTION USING END ===');
                                
                                return $client->id;
                            } catch (\Exception $e) {
                                \Log::error('Exception during client creation:', [
                                    'error' => $e->getMessage(),
                                    'class' => get_class($e)
                                ]);
                                
                                // Verificar si es error de duplicado
                                if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                                    \Log::warning('Duplicate entry detected - searching for existing client');
                                    
                                    // Buscar con consulta SQL directa para evitar cache
                                    $existingClient = DB::selectOne(
                                        'SELECT * FROM clients WHERE company_id = ? AND document_type = ? AND document_number = ?',
                                        [$companyId, $documentType, $documentNumber]
                                    );
                                    
                                    if ($existingClient) {
                                        \Log::info('Found existing client via direct SQL:', ['id' => $existingClient->id]);
                                        
                                        \Filament\Notifications\Notification::make()
                                            ->title(__('Cliente existe'))
                                            ->body(__('Se seleccionó el cliente existente: ' . $existingClient->business_name))
                                            ->success()
                                            ->send();
                                            
                                        return $existingClient->id;
                                    }
                                    
                                    \Log::error('Could not find existing client even with direct SQL');
                                }
                                
                                \Log::error('Rethrowing exception as could not handle it');
                                throw $e;
                            }
                        })
                        ->createOptionAction(function (Action $action) {
                            return $action
                                ->modalHeading(__('Crear Nuevo Cliente'))
                                ->modalSubmitActionLabel(__('Crear Cliente'))
                                ->modalWidth('xl')
                                ->icon('iconoir-user-plus');
                        })
                        ->columnSpan(2),

                    // Documento
                    Select::make('document_type')
                        ->label('Tipo de Documento')
                        ->options([
                            '01' => 'Factura',
                            '03' => 'Boleta de Venta',
                            '07' => 'Nota de Crédito',
                            '08' => 'Nota de Débito',
                            '09' => 'Nota de Venta (Uso Interno)',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // Reset series when document type changes
                            $set('document_series_id', null);
                            $set('series', null);
                            $set('number', null);
                            
                            // Auto-select document series based on document type
                            $companyId = $get('company_id');
                            if ($companyId && $state) {
                                $series = DocumentSeries::query()
                                    ->where('company_id', $companyId)
                                    ->where('document_type', $state)
                                    ->where('status', 'active')
                                    ->orderBy('series')
                                    ->first();
                                
                                if ($series) {
                                    $set('document_series_id', $series->id);
                                    $set('series', $series->series);
                                    $set('number', str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT));
                                }
                            }
                        }),

                    Select::make('document_series_id')
                        ->label(__('Serie de Documento'))
                        ->searchable()
                        ->options(function (callable $get) {
                            $companyId = $get('company_id');
                            $documentType = $get('document_type');
                            
                            if (!$companyId || !$documentType) {
                                return [];
                            }
                            
                            return DocumentSeries::query()
                                ->where('company_id', $companyId)
                                ->where('document_type', $documentType)
                                ->where('status', 'active')
                                ->pluck('series', 'id')
                                ->toArray();
                        })
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $series = DocumentSeries::find($state);
                                if ($series) {
                                    $set('series', $series->series);
                                    $set('number', str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT));
                                }
                            }
                        }),

                        Hidden::make('series')->dehydrated(true),
                        Hidden::make('number')->dehydrated(true),

                    Flatpickr::make('issue_date')
                        ->required()
                        ->label(__('Fecha de Emisión'))
                        ->locale('es')
                        ->dateFormat('Y-m-d')
                        ->displayFormat('d/m/Y')
                        ->maxDate('today')
                        ->default(today())
                        ->suffixIcon('iconoir-calendar'),

                    Hidden::make('issue_time')
                        ->default(fn () => now()->format('H:i:s'))
                        ->dehydrated(true),

                    Flatpickr::make('due_date')
                        ->label(__('Fecha de Vencimiento'))
                        ->locale('es')
                        ->dateFormat('Y-m-d')
                        ->displayFormat('d/m/Y')
                        ->visible(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->required(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->minDate(fn (callable $get) => $get('issue_date') ?: 'today')
                        ->helperText(__('Debe ser posterior a la fecha de emisión'))
                        ->suffixIcon('iconoir-calendar')
                        ->columnSpan(1),

                    // Moneda y Condiciones
                    Select::make('currency_code')
                        ->options([
                            'PEN' => __('Soles (S/)'),
                            'USD' => __('Dólares ($)'),
                        ])
                        ->default('PEN')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state === 'USD') {
                                // Auto-cargar tipo de cambio si existe en cache
                                self::autoLoadExchangeRateFromCache($set);
                            } else {
                                // Resetear a 1 si cambia a PEN
                                $set('exchange_rate', 1.000000);
                            }
                        })
                        ->label(__('Moneda')),

                    TextInput::make('exchange_rate')
                        ->numeric()
                        ->minValue(0.000001)
                        ->step(0.000001)
                        ->default(1.000000)
                        ->label(__('Tipo de Cambio'))
                        ->helperText(__('Se carga automáticamente si está disponible. Use el botón para actualizar.'))
                        ->visible(fn (callable $get) => $get('currency_code') === 'USD')
                        ->suffixAction(
                            Action::make('get_exchange_rate')
                                ->label(__('Obtener TC'))
                                ->icon('heroicon-m-arrow-path')
                                ->color('primary')
                                ->tooltip(__('Actualizar tipo de cambio desde Factiliza'))
                                ->action(function ($set) {
                                    self::getExchangeRateFromFactiliza($set);
                                })
                        ),

                    Select::make('payment_condition')
                        ->options([
                            'immediate' => __('Contado'),
                            'credit' => __('Crédito'),
                        ])
                        ->required()
                        ->native(false)
                        ->reactive()
                        ->label(__('Condición de Pago')),

                    // Credit terms (visible when credit)
                    TextInput::make('additional_data.installments_count')
                        ->label(__('N° de cuotas'))
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->required(fn (callable $get) => $get('payment_condition') === 'credit'),

                    TextInput::make('additional_data.installment_interval_days')
                        ->label(__('Intervalo (días)'))
                        ->numeric()
                        ->minValue(1)
                        ->visible(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->required(fn (callable $get) => $get('payment_condition') === 'credit'),

                    Flatpickr::make('additional_data.first_due_date')
                        ->label(__('Primer vencimiento'))
                        ->locale('es')
                        ->dateFormat('Y-m-d')
                        ->displayFormat('d/m/Y')
                        ->visible(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->required(fn (callable $get) => $get('payment_condition') === 'credit')
                        ->minDate(fn (callable $get) => $get('issue_date') ?: 'today')
                        ->helperText(__('Primera fecha de vencimiento para cuotas'))
                        ->suffixIcon('iconoir-calendar'),

                    Select::make('payment_method')
                        ->options([
                            'cash' => __('Efectivo'),
                            'credit' => __('Crédito'),
                            'transfer' => __('Transferencia'),
                            'card' => __('Tarjeta'),
                        ])
                        ->required()
                        ->native(false)
                        ->label(__('Método de Pago')),

                    Select::make('operation_type')
                        ->options([
                            '0101' => __('Venta Interna'),
                            '0200' => __('Exportación'),
                        ])
                        ->default('0101')
                        ->native(false)
                        ->hidden()
                        ->dehydrated(true)
                        ->columnSpan(2)
                        ->label(__('Tipo de Operación')),

                    Select::make('sunat_status')
                        ->options([
                            'pending' => __('Pendiente'),
                            'accepted' => __('Aceptado'),
                            'rejected' => __('Rechazado'),
                            'observed' => __('Observado'),
                        ])
                        ->default('pending')
                        ->native(false)
                        ->hidden()
                        ->dehydrated(false)
                        ->label(__('Estado SUNAT')),

                    Select::make('status')
                        ->options([
                            'draft' => __('Borrador'),
                            'issued' => __('Emitido'),
                            'paid' => __('Pagado'),
                            'partial' => __('Pago Parcial'),
                            'cancelled' => __('Anulado'),
                        ])
                        ->default('issued')
                        ->native(false)
                        ->hidden()
                        ->dehydrated(false)
                        ->label(__('Estado')),
                ]),

            // 2) Detalle de Productos
            Section::make(__('Detalle de Productos'))
                ->icon('iconoir-shopping-bag')
                ->columns(1)
                ->collapsible()
                ->collapsed(false)
                ->columnSpanFull()
                ->schema([
                    Repeater::make('details')
                        ->relationship('details')
                        ->orderColumn('line_number')
                        ->schema(\App\Filament\Resources\InvoiceResource\Forms\InvoiceDetailForm::make())
                        ->columns(12)
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $product = isset($data['product_id']) ? \App\Models\Product::find($data['product_id']) : null;

                            $quantity = (float) ($data['quantity'] ?? 1);
                            $unitPrice = (float) ($data['unit_price'] ?? 0);
                            $discount = (float) ($data['line_discount_amount'] ?? 0);
                            $gross = $quantity * $unitPrice;
                            $net = max($gross - $discount, 0);
                            $taxType = $data['tax_type'] ?? '10';
                            $igvRate = 0.18;
                            $igvBase = $taxType === '10' ? $net : 0.0;
                            $igvAmount = $taxType === '10' ? $net * $igvRate : 0.0;

                            $data['description'] = $data['description']
                                ?? ($product->description ?? $product->name ?? '');
                            $data['product_code'] = $data['product_code'] ?? ($product->code ?? null);
                            $data['unit_code'] = $data['unit_code'] ?? ($product->unit_code ?? 'NIU');
                            $data['unit_description'] = $data['unit_description'] ?? ($product->unit_description ?? 'UNIDAD (BIENES)');
                            $data['unit_value'] = $data['unit_value'] ?? $unitPrice;
                            $data['gross_amount'] = $gross;
                            $data['net_amount'] = $net;
                            $data['igv_base_amount'] = $igvBase;
                            $data['igv_amount'] = $igvAmount;
                            $data['total_taxes'] = $igvAmount;
                            $data['line_total'] = $net + $igvAmount;
                            $data['is_free'] = (bool) ($data['is_free'] ?? false);

                            return $data;
                        })
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            // Recompute amounts on update to stay consistent
                            $quantity = (float) ($data['quantity'] ?? 1);
                            $unitPrice = (float) ($data['unit_price'] ?? 0);
                            $discount = (float) ($data['line_discount_amount'] ?? 0);
                            $gross = $quantity * $unitPrice;
                            $net = max($gross - $discount, 0);
                            $taxType = $data['tax_type'] ?? '10';
                            $igvRate = 0.18;
                            $igvBase = $taxType === '10' ? $net : 0.0;
                            $igvAmount = $taxType === '10' ? $net * $igvRate : 0.0;

                            $data['unit_value'] = $data['unit_value'] ?? $unitPrice;
                            $data['gross_amount'] = $gross;
                            $data['net_amount'] = $net;
                            $data['igv_base_amount'] = $igvBase;
                            $data['igv_amount'] = $igvAmount;
                            $data['total_taxes'] = $igvAmount;
                            $data['line_total'] = $net + $igvAmount;

                            return $data;
                        })
                        ->defaultItems(1)
                        ->minItems(1)
                        ->maxItems(100)
                        ->label(__('Líneas de Factura'))
                        ->addActionLabel(__('Agregar Producto'))
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => 
                            $state['description'] ?? __('Nueva línea de producto')
                        )
                        ->columnSpanFull(),
                ]),

            // Hidden audit fields
            Hidden::make('created_by')
                ->default(fn () => auth()->id())
                ->dehydrated(true),
            Hidden::make('status')
                ->default('issued')
                ->dehydrated(true),

            // Hidden monetary fields to satisfy NOT NULL constraints
            Hidden::make('subtotal')
                ->default(0)
                ->dehydrated(true),
            Hidden::make('igv_amount')
                ->default(0)
                ->dehydrated(true),
            Hidden::make('total_amount')
                ->default(0)
                ->dehydrated(true),
            Hidden::make('paid_amount')
                ->default(0)
                ->dehydrated(true),
            Hidden::make('pending_amount')
                ->default(0)
                ->dehydrated(true),

            // 3) Resumen
            Section::make(__('Resumen'))
                ->icon('iconoir-calculator')
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    \Filament\Forms\Components\Placeholder::make('subtotal_display')
                        ->label(__('Subtotal'))
                        ->content(function (callable $get) {
                            $details = $get('details') ?? [];
                            $subtotal = 0.0;
                            foreach ($details as $item) {
                                $subtotal += (float) ($item['net_amount'] ?? 0);
                            }
                            $code = $get('currency_code') ?? 'PEN';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::CURRENCY);
                                return $fmt->formatCurrency($subtotal, $code);
                            } catch (\Throwable $e) {
                                $symbol = $code === 'USD' ? '$' : 'S/';
                                return $symbol . ' ' . number_format($subtotal, 2, ',', '.');
                            }
                        })
                        ->columnSpan(['default' => 3, 'md' => 1]),

                    \Filament\Forms\Components\Placeholder::make('igv_display')
                        ->label(__('IGV'))
                        ->content(function (callable $get) {
                            $details = $get('details') ?? [];
                            $igv = 0.0;
                            foreach ($details as $item) {
                                $igv += (float) ($item['igv_amount'] ?? 0);
                            }
                            $code = $get('currency_code') ?? 'PEN';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::CURRENCY);
                                return $fmt->formatCurrency($igv, $code);
                            } catch (\Throwable $e) {
                                $symbol = $code === 'USD' ? '$' : 'S/';
                                return $symbol . ' ' . number_format($igv, 2, ',', '.');
                            }
                        })
                        ->columnSpan(['default' => 3, 'md' => 1]),

                    \Filament\Forms\Components\Placeholder::make('total_display')
                        ->label(__('Total'))
                        ->content(function (callable $get) {
                            $details = $get('details') ?? [];
                            $total = 0.0;
                            foreach ($details as $item) {
                                $total += (float) ($item['line_total'] ?? 0);
                            }
                            $code = $get('currency_code') ?? 'PEN';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::CURRENCY);
                                return $fmt->formatCurrency($total, $code);
                            } catch (\Throwable $e) {
                                $symbol = $code === 'USD' ? '$' : 'S/';
                                return $symbol . ' ' . number_format($total, 2, ',', '.');
                            }
                        })
                        ->columnSpan(['default' => 3, 'md' => 1]),

                    \Filament\Forms\Components\Placeholder::make('paid_display')
                        ->label(__('Pagado'))
                        ->content(function (callable $get) {
                            $paid = (float) ($get('paid_amount') ?? 0);
                            $code = $get('currency_code') ?? 'PEN';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::CURRENCY);
                                return $fmt->formatCurrency($paid, $code);
                            } catch (\Throwable $e) {
                                $symbol = $code === 'USD' ? '$' : 'S/';
                                return $symbol . ' ' . number_format($paid, 2, ',', '.');
                            }
                        })
                        ->columnSpan(['default' => 3, 'md' => 1]),

                    \Filament\Forms\Components\Placeholder::make('pending_display')
                        ->label(__('Pendiente'))
                        ->content(function (callable $get) {
                            $details = $get('details') ?? [];
                            $total = 0.0;
                            foreach ($details as $item) {
                                $total += (float) ($item['line_total'] ?? 0);
                            }
                            $paid = (float) ($get('paid_amount') ?? 0);
                            $pending = max($total - $paid, 0);
                            $code = $get('currency_code') ?? 'PEN';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::CURRENCY);
                                return $fmt->formatCurrency($pending, $code);
                            } catch (\Throwable $e) {
                                $symbol = $code === 'USD' ? '$' : 'S/';
                                return $symbol . ' ' . number_format($pending, 2, ',', '.');
                            }
                        })
                        ->columnSpan(['default' => 3, 'md' => 1]),
                    \Filament\Forms\Components\Placeholder::make('total_in_words')
                        ->label(__('Total en letras'))
                        ->content(function (callable $get) {
                            $details = $get('details') ?? [];
                            $total = 0.0;
                            foreach ($details as $item) {
                                $total += (float) ($item['line_total'] ?? 0);
                            }
                            $code = $get('currency_code') ?? 'PEN';
                            $currencyWord = $code === 'USD' ? 'DOLARES' : 'SOLES';
                            try {
                                $fmt = new \NumberFormatter('es_PE', \NumberFormatter::SPELLOUT);
                                $int = (int) floor($total);
                                $cents = (int) round(($total - $int) * 100);
                                $words = strtoupper($fmt->format($int));
                                $centsStr = str_pad((string) $cents, 2, '0', STR_PAD_LEFT);
                                return 'SON ' . $words . ' CON ' . $centsStr . '/100 ' . $currencyWord;
                            } catch (\Throwable $e) {
                                return 'SON ' . number_format($total, 2, ',', '.') . ' ' . $currencyWord;
                            }
                        })
                        ->columnSpan(3),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_number')
                    ->searchable(['series', 'number'])
                    ->sortable()
                    ->label(__('Número')),
                    
                TextColumn::make('document_type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '01' => __('Factura'),
                        '03' => __('Boleta'),
                        '07' => __('Nota de Crédito'),
                        '08' => __('Nota de Débito'),
                        '09' => __('Nota de Venta'),
                        default => $state,
                    })
                    ->label(__('Tipo')),
                    
                TextColumn::make('details_count')
                    ->counts('details')
                    ->label(__('Items')),
                    
                TextColumn::make('client.business_name')
                    ->searchable()
                    ->sortable()
                    ->label(__('Cliente')),
                    
                TextColumn::make('total_amount')
                    ->money(fn (Invoice $record): string => $record->currency_code)
                    ->sortable()
                    ->label(__('Total')),

                IconColumn::make('is_paid')
                    ->label(__('Pagado'))
                    ->boolean()
                    ->state(fn (Invoice $record): bool => ($record->status === 'paid') || ((float) $record->pending_amount) <= 0)
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->color(fn (Invoice $record): string => ($record->status === 'paid' || ((float) $record->pending_amount) <= 0) ? 'success' : (((float) $record->paid_amount) > 0 ? 'warning' : 'gray')),

                TextColumn::make('pending_amount')
                    ->money(fn (Invoice $record): string => $record->currency_code)
                    ->label(__('Pendiente'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('issue_date')
                    ->date()
                    ->sortable()
                    ->label(__('Fecha Emisión')),
                    
                TextColumn::make('currency_code')
                    ->label(__('Moneda')),
                    
                TextColumn::make('sunat_status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('Pendiente'),
                        'accepted' => __('Aceptado'),
                        'rejected' => __('Rechazado'),
                        'exception' => __('Excepción'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'observed' => 'warning',
                        default => 'gray',
                    })
                    ->label(__('SUNAT')),
                    
                TextColumn::make('status')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => __('Borrador'),
                        'issued' => __('Emitido'),
                        'paid' => __('Pagado'),
                        'partial_paid' => __('Pago Parcial'),
                        'cancelled' => __('Anulado'),
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'info',
                        'paid' => 'success',
                        'partial_paid' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->label(__('Estado')),
                    
                TextColumn::make('company.business_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('Empresa')),
            ])
            ->defaultSort('issue_date', 'desc')
            ->filters([
                SelectFilter::make('document_type')
                    ->options([
                        '01' => __('Factura'),
                        '03' => __('Boleta'),
                        '07' => __('Nota de Crédito'),
                        '08' => __('Nota de Débito'),
                        '09' => __('Nota de Venta'),
                    ])
                    ->label(__('Tipo de Documento')),
                    
                SelectFilter::make('sunat_status')
                    ->options([
                        'pending' => __('Pendiente'),
                        'accepted' => __('Aceptado'),
                        'rejected' => __('Rechazado'),
                        'exception' => __('Excepción'),
                    ])
                    ->label(__('Estado SUNAT')),
                    
                SelectFilter::make('status')
                    ->options([
                        'draft' => __('Borrador'),
                        'issued' => __('Emitido'),
                        'paid' => __('Pagado'),
                        'partial_paid' => __('Pago Parcial'),
                        'cancelled' => __('Anulado'),
                    ])
                    ->label(__('Estado')),
                    
                SelectFilter::make('company_id')
                    ->relationship('company', 'business_name')
                    ->searchable()
                    ->preload()
                    ->label(__('Empresa')),
                    
                Filter::make('issue_date')
                    ->form([
                        DatePicker::make('issue_date_from')
                            ->label(__('Desde')),
                        DatePicker::make('issue_date_to')
                            ->label(__('Hasta')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['issue_date_from'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['issue_date_to'],
                                fn (Builder $query, $date): Builder =>
                                    $query->whereDate('issue_date', '<=', $date),
                            );
                    })
                    ->label(__('Fecha de Emisión')),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    // === OPCIONES DE IMPRESIÓN ===
                    Action::make('print_a4')
                        ->label(__('Imprimir A4'))
                        ->icon('heroicon-o-printer')
                        ->color('primary')
                        ->url(fn (Invoice $record): string => route('invoices.pdf.download', $record))
                        ->openUrlInNewTab(),
                    Action::make('print_ticket')
                        ->label(__('Imprimir Ticket'))
                        ->icon('heroicon-o-receipt-percent')
                        ->color('success')
                        ->url(fn (Invoice $record): string => route('invoices.ticket.download', $record))
                        ->openUrlInNewTab(),
                    // === OPCIONES DE VISTA PREVIA ===
                    MediaAction::make('preview_a4')
                        ->label(__('Vista Previa A4'))
                        ->icon('heroicon-o-document-magnifying-glass')
                        ->color('info')
                        ->media(fn (Invoice $record): string => route('invoices.pdf.view', $record))
                        ->mediaType('pdf')
                        ->disableDownload()
                        ->disableFullscreen(false)
                        ->modalWidth('5xl')
                        ->modalHeading(fn (Invoice $record): string => 
                            match($record->document_type) {
                                '01' => 'Vista Previa A4 - Factura ' . $record->full_number,
                                '03' => 'Vista Previa A4 - Boleta ' . $record->full_number,
                                '07' => 'Vista Previa A4 - Nota de Crédito ' . $record->full_number,
                                '08' => 'Vista Previa A4 - Nota de Débito ' . $record->full_number,
                                '09' => 'Vista Previa A4 - Nota de Venta ' . $record->full_number,
                                default => 'Vista Previa A4 - Comprobante ' . $record->full_number
                            }
                        )
                        ->modalDescription(fn (Invoice $record): string => 
                            'Cliente: ' . $record->client_business_name . ' | ' .
                            'Fecha: ' . $record->issue_date->format('d/m/Y') . ' | ' .
                            'Total: ' . ($record->currency_code === 'USD' ? 'US$ ' : 'S/ ') . number_format($record->total_amount, 2)
                        )
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Cerrar')
                        ->slideOver(false)
                        ->extraModalWindowAttributes([
                            'class' => 'pdf-preview-modal',
                            'style' => 'z-index: 9999;'
                        ]),
                    MediaAction::make('preview_ticket')
                        ->label(__('Vista Previa Ticket'))
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->media(fn (Invoice $record): string => route('invoices.ticket.view', $record))
                        ->mediaType('pdf')
                        ->disableDownload()
                        ->modalWidth('3xl')
                        ->modalHeading(fn (Invoice $record): string => 'Vista Previa Ticket - ' . $record->full_number)
                        ->modalDescription(fn (Invoice $record): string => 'Formato 80mm para impresoras térmicas'),
                    // === OPCIONES DE ENVÍO ELECTRÓNICO ===
                    Action::make('send_to_sunat')
                        ->label(__('Enviar a SUNAT'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->visible(fn (Invoice $record): bool => 
                            in_array($record->document_type, ['01', '03', '07', '08']) && 
                            $record->sunat_status !== 'accepted'
                        )
                        ->requiresConfirmation()
                        ->modalHeading(__('Enviar Documento a SUNAT'))
                        ->modalDescription(function (Invoice $record): string {
                            $docType = match($record->document_type) {
                                '01' => 'Factura',
                                '03' => 'Boleta de Venta', 
                                '07' => 'Nota de Crédito',
                                '08' => 'Nota de Débito',
                                default => 'Documento'
                            };
                            return __('¿Está seguro de enviar el :type :number a SUNAT?', [
                                'type' => $docType,
                                'number' => $record->full_number
                            ]);
                        })
                        ->modalSubmitActionLabel(__('Enviar a SUNAT'))
                        ->modalIcon('heroicon-o-paper-airplane')
                        ->action(function (Invoice $record): void {
                            try {
                                $service = app(\App\Services\ElectronicInvoiceService::class);
                                
                                $result = match($record->document_type) {
                                    '01' => $service->sendFactura($record),
                                    '03' => $service->sendBoleta($record),
                                    '07' => $service->sendNotaCredito($record),
                                    '08' => $service->sendNotaDebito($record),
                                    default => throw new \Exception('Tipo de documento no soportado')
                                };

                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Documento enviado exitosamente'))
                                        ->body($result['message'] ?? __('El documento fue aceptado por SUNAT'))
                                        ->success()
                                        ->duration(5000)
                                        ->send();
                                } else {
                                    // Construir mensaje detallado del error
                                    $errorMessage = $result['error']['message'] ?? __('Error desconocido');
                                    $errorCode = $result['error']['code'] ?? 'N/A';
                                    $qpseRaw = $result['qpse_raw'] ?? null;
                                    
                                    $detailedMessage = "Código: {$errorCode}\nMensaje: {$errorMessage}";
                                    
                                    if ($qpseRaw) {
                                        $detailedMessage .= "\n\nRespuesta QPse:\n" . json_encode($qpseRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    }

                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Error al enviar documento'))
                                        ->body($detailedMessage)
                                        ->danger()
                                        ->duration(15000)
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('Error del sistema'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->duration(10000)
                                    ->send();
                            }
                        }),

                    Action::make('resend_to_sunat')
                        ->label(__('Reenviar a SUNAT'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (Invoice $record): bool => 
                            in_array($record->document_type, ['01', '03', '07', '08']) && 
                            in_array($record->sunat_status, ['rejected', 'observed', 'pending'])
                        )
                        ->requiresConfirmation()
                        ->modalHeading(__('Reenviar Documento a SUNAT'))
                        ->modalDescription(__('¿Está seguro de reenviar este documento a SUNAT?'))
                        ->modalSubmitActionLabel(__('Reenviar'))
                        ->action(function (Invoice $record): void {
                            try {
                                $service = app(\App\Services\ElectronicInvoiceService::class);
                                $result = $service->resendDocument($record);

                                if ($result['success']) {
                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Documento reenviado exitosamente'))
                                        ->body($result['message'] ?? __('El documento fue aceptado por SUNAT'))
                                        ->success()
                                        ->send();
                                } else {
                                    // Construir mensaje detallado del error para reenvío
                                    $errorMessage = $result['error']['message'] ?? __('Error desconocido');
                                    $errorCode = $result['error']['code'] ?? 'N/A';
                                    $qpseRaw = $result['qpse_raw'] ?? null;
                                    
                                    $detailedMessage = "Código: {$errorCode}\nMensaje: {$errorMessage}";
                                    
                                    if ($qpseRaw) {
                                        $detailedMessage .= "\n\nRespuesta QPse:\n" . json_encode($qpseRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    }

                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Error al reenviar documento'))
                                        ->body($detailedMessage)
                                        ->danger()
                                        ->duration(15000)
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('Error del sistema'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('check_sunat_status')
                        ->label(__('Consultar Estado'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('info')
                        ->visible(fn (Invoice $record): bool => 
                            in_array($record->document_type, ['01', '03', '07', '08']) && 
                            $record->sunat_status !== 'pending'
                        )
                        ->action(function (Invoice $record): void {
                            try {
                                $service = app(\App\Services\ElectronicInvoiceService::class);
                                $result = $service->getDocumentStatus($record);

                                if ($result['success']) {
                                    $status = $result['status'];
                                    $message = is_array($status) ? 
                                        json_encode($status, JSON_PRETTY_PRINT) : 
                                        (string) $status;

                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Estado del documento'))
                                        ->body($message)
                                        ->info()
                                        ->duration(8000)
                                        ->send();
                                } else {
                                    // Construir mensaje detallado del error para consulta
                                    $errorMessage = $result['error']['message'] ?? __('Error desconocido');
                                    $errorCode = $result['error']['code'] ?? 'N/A';
                                    $qpseRaw = $result['qpse_raw'] ?? null;
                                    
                                    $detailedMessage = "Código: {$errorCode}\nMensaje: {$errorMessage}";
                                    
                                    if ($qpseRaw) {
                                        $detailedMessage .= "\n\nRespuesta QPse:\n" . json_encode($qpseRaw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                    }

                                    \Filament\Notifications\Notification::make()
                                        ->title(__('Error al consultar estado'))
                                        ->body($detailedMessage)
                                        ->warning()
                                        ->duration(15000)
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('Error del sistema'))
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('register_payment')
                        ->label(__('Registrar pago'))
                        ->icon('heroicon-o-credit-card')
                        ->visible(fn (Invoice $record): bool => (float) $record->pending_amount > 0)
                        ->form([
                            Section::make(__('💰 Resumen del Documento'))
                                ->icon('heroicon-o-document-text')
                                ->description(__('Información financiera del comprobante'))
                                ->columns(4)
                                ->schema([
                                    \Filament\Forms\Components\Placeholder::make('currency_display')
                                        ->label(__('Moneda'))
                                        ->content(function (Invoice $record): string {
                                            $currencyName = match ($record->currency_code) {
                                                'USD' => 'Dólares Americanos',
                                                'PEN' => 'Soles Peruanos',
                                                default => 'Soles Peruanos',
                                            };
                                            $flag = match ($record->currency_code) {
                                                'USD' => '🇺🇸',
                                                'PEN' => '🇵🇪',
                                                default => '🇵🇪',
                                            };
                                            return $flag . ' ' . $record->currency_code . ' — ' . $currencyName;
                                        })
                                        ->columnSpan(1),

                                    \Filament\Forms\Components\Placeholder::make('invoice_total_display')
                                        ->label(__('Total del Documento'))
                                        ->content(function (Invoice $record): string {
                                            $code = $record->currency_code ?? 'PEN';
                                            $total = (float) ($record->total_amount ?? 0);
                                            $symbol = $code === 'USD' ? '$' : 'S/';
                                            return $symbol . ' ' . number_format($total, 2, '.', ',');
                                        })
                                        ->columnSpan(1),

                                    \Filament\Forms\Components\Placeholder::make('invoice_paid_display')
                                        ->label(__('Pagado'))
                                        ->content(function (Invoice $record): string {
                                            $code = $record->currency_code ?? 'PEN';
                                            $paid = (float) ($record->paid_amount ?? 0);
                                            $symbol = $code === 'USD' ? '$' : 'S/';
                                            return $symbol . ' ' . number_format($paid, 2, '.', ',');
                                        })
                                        ->columnSpan(1),

                                    \Filament\Forms\Components\Placeholder::make('invoice_pending_display')
                                        ->label(__('Pendiente'))
                                        ->content(function (Invoice $record): string {
                                            $code = $record->currency_code ?? 'PEN';
                                            $pending = (float) ($record->pending_amount ?? 0);
                                            $symbol = $code === 'USD' ? '$' : 'S/';
                                            return $symbol . ' ' . number_format($pending, 2, '.', ',');
                                        })
                                        ->columnSpan(1),
                                ]),

                            Section::make(__('💳 Datos del Pago'))
                                ->icon('heroicon-o-credit-card')
                                ->description(__('Ingrese los detalles del pago a registrar'))
                                ->columns(2)
                                ->schema([
                                    Select::make('installment_id')
                                        ->label(__('Cuota a Pagar'))
                                        ->placeholder(__('Seleccionar cuota específica (opcional)'))
                                        ->options(function (Invoice $record): array {
                                            return $record->paymentInstallments()
                                                ->where('pending_amount', '>', 0)
                                                ->orderBy('installment_number')
                                                ->get()
                                                ->mapWithKeys(function ($i) use ($record) {
                                                    $symbol = $record->currency_code === 'USD' ? '$' : 'S/';
                                                    $amount = number_format((float) $i->pending_amount, 2, '.', ',');
                                                    return [
                                                        $i->id => "Cuota #{$i->installment_number} — {$symbol} {$amount}"
                                                    ];
                                                })->all();
                                        })
                                        ->visible(fn (Invoice $record): bool => $record->paymentInstallments()->where('pending_amount', '>', 0)->exists())
                                        ->searchable()
                                        ->native(false)
                                        ->columnSpan(1),

                                    TextInput::make('amount')
                                        ->label(__('Monto a Pagar'))
                                        ->placeholder(__('0.00'))
                                        ->numeric()
                                        ->minValue(0.01)
                                        ->prefix(fn (Invoice $record) => $record->currency_code === 'USD' ? '$' : 'S/')
                                        ->maxValue(function (callable $get, Invoice $record): float {
                                            $installmentId = $get('installment_id');
                                            if ($installmentId) {
                                                $inst = $record->paymentInstallments()->find($installmentId);
                                                return (float) ($inst->pending_amount ?? 0);
                                            }
                                            return (float) $record->pending_amount;
                                        })
                                        ->required()
                                        ->columnSpan(1),

                                    \Filament\Forms\Components\DateTimePicker::make('paid_at')
                                        ->label(__('Fecha y Hora del Pago'))
                                        ->seconds(false)
                                        ->default(now())
                                        ->required()
                                        ->columnSpan(1),

                                    TextInput::make('reference')
                                        ->label(__('Referencia del Pago'))
                                        ->placeholder(__('Ej: Transferencia #123456, Efectivo, etc.'))
                                        ->maxLength(100)
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->action(function (Invoice $record, array $data): void {
                            \Illuminate\Support\Facades\DB::transaction(function () use ($record, $data) {
                                $amount = (float) ($data['amount'] ?? 0);
                                $installmentId = $data['installment_id'] ?? null;
                                if ($amount <= 0) {
                                    return; // validation should catch
                                }

                                if ($installmentId) {
                                    // Abonar a una cuota existente
                                    $installment = $record->paymentInstallments()->findOrFail($installmentId);
                                    $installment->markAsPaid($amount, $data['reference'] ?? null);
                                    if (! empty($data['paid_at'])) {
                                        $installment->paid_at = $data['paid_at'];
                                        $installment->saveQuietly();
                                    }
                                } else {
                                    // No hay cuotas (contado): crear una cuota única y abonar
                                    $nextNumber = ($record->paymentInstallments()->max('installment_number') ?? 0) + 1;
                                    $new = $record->paymentInstallments()->create([
                                        'installment_number' => $nextNumber,
                                        'amount' => max($amount, (float) $record->pending_amount),
                                        'due_date' => now()->toDateString(),
                                        'paid_amount' => 0,
                                        'pending_amount' => 0, // se recalcula en observer
                                        'status' => 'pending',
                                        'paid_at' => $data['paid_at'] ?? null,
                                        'payment_reference' => $data['reference'] ?? null,
                                    ]);
                                    $new->markAsPaid($amount, $data['reference'] ?? null);
                                    if (! empty($data['paid_at'])) {
                                        $new->paid_at = $data['paid_at'];
                                        $new->saveQuietly();
                                    }
                                }
                            });
                        })
                        ->successNotificationTitle(__('Pago registrado')),
                    DeleteAction::make(),
                ])->label(__('Opciones')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // Envío masivo a SUNAT
                    BulkAction::make('bulk_send_to_sunat')
                        ->label(__('Enviar a SUNAT'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading(__('Envío Masivo a SUNAT'))
                        ->modalDescription(__('¿Está seguro de enviar los documentos seleccionados a SUNAT? Esta acción procesará solo documentos electrónicos pendientes.'))
                        ->modalSubmitActionLabel(__('Enviar Documentos'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $service = app(\App\Services\ElectronicInvoiceService::class);
                            
                            // Filtrar solo documentos electrónicos pendientes
                            $eligibleRecords = $records->filter(function (Invoice $record) {
                                return in_array($record->document_type, ['01', '03', '07', '08']) && 
                                       $record->sunat_status !== 'accepted';
                            });

                            if ($eligibleRecords->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('Sin documentos elegibles'))
                                    ->body(__('Ninguno de los documentos seleccionados puede ser enviado a SUNAT'))
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $total = $eligibleRecords->count();
                            $success = 0;
                            $errors = 0;

                            foreach ($eligibleRecords as $record) {
                                try {
                                    $result = match($record->document_type) {
                                        '01' => $service->sendFactura($record),
                                        '03' => $service->sendBoleta($record),
                                        '07' => $service->sendNotaCredito($record),
                                        '08' => $service->sendNotaDebito($record),
                                        default => ['success' => false, 'error' => ['message' => 'Tipo no soportado']]
                                    };

                                    if ($result['success']) {
                                        $success++;
                                    } else {
                                        $errors++;
                                    }
                                } catch (\Exception $e) {
                                    $errors++;
                                }

                                // Pequeña pausa para no saturar el servicio
                                usleep(250000); // 0.25 segundos
                            }

                            $message = "Procesados: {$total} | Exitosos: {$success} | Errores: {$errors}";
                            
                            // Si hay errores, mostrar más detalles
                            if ($errors > 0) {
                                $message .= "\n\n💡 Revisar logs para detalles específicos de errores:\ntail -f storage/logs/laravel.log";
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('Envío masivo completado'))
                                ->body($message)
                                ->success($errors === 0)
                                ->warning($errors > 0 && $success > 0)
                                ->danger($errors > 0 && $success === 0)
                                ->duration(10000)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    // Reenvío masivo de documentos rechazados
                    BulkAction::make('bulk_resend_to_sunat')
                        ->label(__('Reenviar a SUNAT'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Reenvío Masivo a SUNAT'))
                        ->modalDescription(__('¿Está seguro de reenviar los documentos seleccionados a SUNAT? Esta acción procesará solo documentos rechazados o con excepciones.'))
                        ->modalSubmitActionLabel(__('Reenviar Documentos'))
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records): void {
                            $service = app(\App\Services\ElectronicInvoiceService::class);
                            
                            // Filtrar solo documentos rechazados o con excepciones
                            $eligibleRecords = $records->filter(function (Invoice $record) {
                                return in_array($record->document_type, ['01', '03', '07', '08']) && 
                                       in_array($record->sunat_status, ['rejected', 'observed', 'pending']);
                            });

                            if ($eligibleRecords->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('Sin documentos elegibles'))
                                    ->body(__('Ninguno de los documentos seleccionados puede ser reenviado'))
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $total = $eligibleRecords->count();
                            $success = 0;
                            $errors = 0;

                            foreach ($eligibleRecords as $record) {
                                try {
                                    $result = $service->resendDocument($record);

                                    if ($result['success']) {
                                        $success++;
                                    } else {
                                        $errors++;
                                    }
                                } catch (\Exception $e) {
                                    $errors++;
                                }

                                // Pausa más larga para reenvíos
                                usleep(500000); // 0.5 segundos
                            }

                            $message = "Reprocesados: {$total} | Recuperados: {$success} | Con errores: {$errors}";
                            
                            // Si hay errores, mostrar más detalles
                            if ($errors > 0) {
                                $message .= "\n\n💡 Revisar logs para detalles específicos de errores:\ntail -f storage/logs/laravel.log";
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('Reenvío masivo completado'))
                                ->body($message)
                                ->success($errors === 0)
                                ->warning($errors > 0 && $success > 0)
                                ->danger($errors > 0 && $success === 0)
                                ->duration(10000)
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InvoiceDetailsRelationManager::class,
            RelationManagers\PaymentInstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
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
     * Buscar datos en Factiliza para modal de creación de cliente
     */
    protected static function searchFactilizaDataForModal($documentNumber, $documentType, $set): void
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
     * Auto-cargar tipo de cambio desde cache al seleccionar USD
     */
    protected static function autoLoadExchangeRateFromCache($set): void
    {
        try {
            // Verificar si existe tipo de cambio en cache para hoy
            $today = now()->toDateString();
            $cached = \App\Models\ExchangeRate::getForDate($today);
            
            if ($cached) {
                // Cargar desde cache
                $exchangeRate = (float) $cached->sell_rate;
                $set('exchange_rate', $exchangeRate);
                
                \Filament\Notifications\Notification::make()
                    ->title(__('Tipo de cambio cargado'))
                    ->body(__('TC del :fecha: S/ :rate (desde cache)', [
                        'fecha' => $cached->date->format('Y-m-d'),
                        'rate' => number_format($exchangeRate, 6)
                    ]))
                    ->success()
                    ->duration(3000)
                    ->send();
            } else {
                // No hay cache, mantener valor por defecto y mostrar helper
                $set('exchange_rate', 1.000000);
                
                \Filament\Notifications\Notification::make()
                    ->title(__('Tipo de cambio no disponible'))
                    ->body(__('Use el botón "Obtener TC" para consultar el tipo de cambio actual'))
                    ->info()
                    ->duration(4000)
                    ->send();
            }
        } catch (\Exception $e) {
            \Log::error('Error al auto-cargar tipo de cambio desde cache', [
                'error' => $e->getMessage()
            ]);
            
            // En caso de error, mantener valor por defecto
            $set('exchange_rate', 1.000000);
        }
    }

    /**
     * Obtener tipo de cambio desde Factiliza
     */
    protected static function getExchangeRateFromFactiliza($set): void
    {
        try {
            // Llamar al servicio de Factiliza
            $factilizaService = app(\App\Services\FactilizaService::class);
            $result = $factilizaService->consultarTipoCambio();
            
            if ($result['success'] && $result['data']) {
                $data = $result['data'];
                $exchangeRate = (float) ($data['venta'] ?? 1.000000);
                
                // Establecer el tipo de cambio
                $set('exchange_rate', $exchangeRate);
                
                $source = isset($data['cached']) && $data['cached'] ? 'cache' : 'API';
                $sourceText = $source === 'cache' ? 'desde cache' : 'desde API';
                
                \Filament\Notifications\Notification::make()
                    ->title(__('Tipo de cambio actualizado'))
                    ->body(__('TC del :fecha: S/ :rate (:source)', [
                        'fecha' => $data['fecha'] ?? date('Y-m-d'),
                        'rate' => number_format($exchangeRate, 6),
                        'source' => $sourceText
                    ]))
                    ->success()
                    ->duration(5000)
                    ->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title(__('Error al obtener tipo de cambio'))
                    ->body($result['message'] ?? __('No se pudo consultar el tipo de cambio'))
                    ->warning()
                    ->send();
            }
        } catch (\Exception $e) {
            \Log::error('Error al consultar tipo de cambio en factura', [
                'error' => $e->getMessage()
            ]);
            
            \Filament\Notifications\Notification::make()
                ->title(__('Error de conexión'))
                ->body(__('No se pudo conectar con el servicio de tipo de cambio'))
                ->danger()
                ->send();
        }
    }
}