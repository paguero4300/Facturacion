<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use App\Services\CompanyApiService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\BulkActionGroup;
use BackedEnum;
use UnitEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static BackedEnum|string|null $navigationIcon = 'iconoir-building';
    
    protected static string|UnitEnum|null $navigationGroup = 'Administración';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'business_name';

    public static function getNavigationLabel(): string
    {
        return __('Empresas');
    }

    public static function getModelLabel(): string
    {
        return __('Empresa');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Empresas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('company_tabs')
                    ->tabs([
                        Tab::make('basic_info')
                            ->label(__('📋 Información Básica'))
                            ->icon('heroicon-o-building-office')
                            ->schema([
                                Section::make(__('🏢 Datos de la Empresa'))
                                    ->description(__('Información principal de la empresa registrada en SUNAT'))
                                    ->icon('heroicon-o-building-office-2')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('ruc')
                                                    ->required()
                                                    ->maxLength(11)
                                                    ->minLength(11)
                                                    ->label(__('RUC'))
                                                    ->placeholder('20123456789')
                                                    ->suffixAction(
                                                        Action::make('search_ruc')
                                                            ->label(__('Buscar'))
                                                            ->icon('heroicon-m-magnifying-glass')
                                                            ->color('primary')
                                                            ->extraAttributes([
                                                                'wire:loading.attr' => 'disabled',
                                                                'wire:loading.class' => 'opacity-50 cursor-not-allowed',
                                                                'wire:loading.class.remove' => 'hover:bg-primary-500',
                                                                'wire:target' => 'callMountedAction',
                                                            ])
                                                            ->action(function ($state, $set, $get) {
                                                                if (!$state) {
                                                                    \Filament\Notifications\Notification::make()
                                                                        ->title(__('RUC requerido'))
                                                                        ->body(__('Ingrese el RUC para consultar'))
                                                                        ->warning()
                                                                        ->send();
                                                                    return;
                                                                }
                                                                
                                                                if (strlen($state) !== 11 || !is_numeric($state)) {
                                                                    \Filament\Notifications\Notification::make()
                                                                        ->title(__('Formato inválido'))
                                                                        ->body(__('RUC debe tener 11 dígitos'))
                                                                        ->danger()
                                                                        ->send();
                                                                    return;
                                                                }
                                                                
                                                                // Usar el mismo patrón que ClientResource
                                                                $factilizaService = app(\App\Services\FactilizaService::class);
                                                                $result = $factilizaService->consultarRuc($state);
                                                                
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
                                                                        ->body(__('Información actualizada desde Factiliza'))
                                                                        ->success()
                                                                        ->send();
                                                                } else {
                                                                    \Filament\Notifications\Notification::make()
                                                                        ->title(__('RUC no encontrado'))
                                                                        ->body($result['message'] ?? __('No se encontraron datos para este RUC'))
                                                                        ->warning()
                                                                        ->send();
                                                                }
                                                            })
                                                    ),
                                                    
                                                TextInput::make('business_name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->label(__('Razón Social'))
                                                    ->columnSpan(2),
                                            ]),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('commercial_name')
                                                    ->maxLength(255)
                                                    ->label(__('Nombre Comercial')),
                                                    
                                                Select::make('tax_regime')
                                                    ->options([
                                                        'general' => __('Régimen General'),
                                                        'special' => __('Régimen Especial'),
                                                        'mype' => __('Régimen MYPE Tributario'),
                                                        'simplified' => __('Régimen Simplificado'),
                                                    ])
                                                    ->default('general')
                                                    ->label(__('Régimen Tributario'))
                                                    ->native(false),
                                            ]),
                                    ]),

                                Section::make(__('📍 Ubicación y Contacto'))
                                    ->description(__('Datos de ubicación y medios de contacto'))
                                    ->icon('heroicon-o-map-pin')
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('address')
                                            ->maxLength(255)
                                            ->label(__('Dirección'))
                                            ->columnSpanFull(),
                                            
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('district')
                                                    ->maxLength(100)
                                                    ->label(__('Distrito')),
                                                    
                                                TextInput::make('province')
                                                    ->maxLength(100)
                                                    ->label(__('Provincia')),
                                                    
                                                TextInput::make('department')
                                                    ->maxLength(100)
                                                    ->label(__('Departamento')),
                                                    
                                                TextInput::make('ubigeo')
                                                    ->maxLength(10)
                                                    ->label(__('Ubigeo'))
                                                    ->placeholder('150101'),
                                            ]),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('phone')
                                                    ->tel()
                                                    ->maxLength(20)
                                                    ->label(__('Teléfono'))
                                                    ->placeholder('+51 999 999 999'),
                                                    
                                                TextInput::make('email')
                                                    ->email()
                                                    ->maxLength(100)
                                                    ->label(__('Email'))
                                                    ->placeholder('empresa@ejemplo.com'),
                                            ]),
                                    ]),
                                    
                                Section::make(__('⚙️ Estado'))
                                    ->description(__('Configuración general del estado de la empresa'))
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->schema([
                                        Toggle::make('status')
                                            ->default(true)
                                            ->label(__('Empresa Activa'))
                                            ->helperText(__('Desactivar una empresa ocultará sus documentos del sistema')),
                                    ]),
                            ]),

                        Tab::make('electronic_billing')
                            ->label(__('⚡ Facturación Electrónica'))
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Section::make(__('🏛️ Configuración SUNAT'))
                                    ->description(__('Configuración para el envío de documentos electrónicos a SUNAT'))
                                    ->icon('heroicon-o-building-library')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('ose_provider')
                                                    ->options([
                                                        'sunat' => __('SUNAT (Directo)'),
                                                        'qpse' => __('QPse'),
                                                        'factiliza' => __('Factiliza'),
                                                        'nubefact' => __('NubeFact'),
                                                        'other' => __('Otro Proveedor'),
                                                    ])
                                                    ->default('qpse')
                                                    ->label(__('Proveedor OSE'))
                                                    ->native(false)
                                                    ->reactive()
                                                    ->helperText(__('Operador de Servicios Electrónicos')),
                                                    
                                                Toggle::make('sunat_production')
                                                    ->default(false)
                                                    ->label(__('Modo Producción'))
                                                    ->helperText(__('Activar solo para envíos reales a SUNAT')),
                                            ]),
                                    ]),

                                Section::make(__('🔑 QPse - Gestión de Tokens'))
                                    ->description(__('Obtención y renovación de tokens de acceso QPse usando credenciales configuradas'))
                                    ->icon('heroicon-o-key')
                                    ->visible(fn ($get) => $get('ose_provider') === 'qpse')
                                    ->schema([
                                        Placeholder::make('qpse_info')
                                            ->label(__('ℹ️ Información'))
                                            ->content(__('Los tokens se obtienen usando las credenciales configuradas en la sección "QPse - Configuración" más abajo.'))
                                            ->columnSpanFull(),
                                            
                                        Actions::make([
                                            Action::make('obtener_token_qpse')
                                                ->label(__('🎫 Obtener Token de Acceso'))
                                                ->icon('heroicon-o-key')
                                                ->color('primary')
                                                ->size('lg')
                                                ->action(function ($record) {
                                                    // Mostrar notificación de progreso
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Obteniendo token...'))
                                                        ->body(__('Conectando con QPse usando credenciales configuradas'))
                                                        ->info()
                                                        ->duration(3000)
                                                        ->send();
                                                    
                                                    $tokenService = app(\App\Services\QpseTokenService::class);
                                                    // Obtener token sin prueba de conexión para evitar errores de endpoints
                                                    $result = $tokenService->obtenerTokenConCredencialesExistentes($record, false);
                                                    
                                                    if ($result['success']) {
                                                        if (isset($result['warning']) && $result['warning']) {
                                                            // Token obtenido pero con advertencias
                                                            \Filament\Notifications\Notification::make()
                                                                ->title(__('⚠️ Token Obtenido con Advertencias'))
                                                                ->body($result['message'] . "\n\nError de conexión: " . ($result['connection_error'] ?? 'Desconocido'))
                                                                ->warning()
                                                                ->duration(10000)
                                                                ->send();
                                                        } else {
                                                            // Todo exitoso
                                                            $expiresInfo = '';
                                                            if (isset($result['data']['expires_in_hours'])) {
                                                                $expiresInfo = " (Expira en {$result['data']['expires_in_hours']} horas)";
                                                            }
                                                            
                                                            \Filament\Notifications\Notification::make()
                                                                ->title(__('✅ Token QPse Obtenido'))
                                                                ->body($result['message'] . $expiresInfo)
                                                                ->success()
                                                                ->duration(8000)
                                                                ->send();
                                                        }
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('❌ Error Obteniendo Token'))
                                                            ->body($result['error']['message'] ?? __('Error desconocido obteniendo token'))
                                                            ->danger()
                                                            ->duration(10000)
                                                            ->send();
                                                    }
                                                }),
                                                
                                            Action::make('view_qpse_status_complete')
                                                ->label(__('📊 Estado y Diagnóstico QPse'))
                                                ->icon('heroicon-o-chart-bar-square')
                                                ->color('info')
                                                ->action(function ($record) {
                                                    $tokenService = app(\App\Services\QpseTokenService::class);
                                                    $status = $tokenService->getCompleteStatus($record);
                                                    
                                                    // Construir mensaje de estado
                                                    $statusText = "🏢 RUC: {$status['ruc']}\n";
                                                    $statusText .= "🔌 Proveedor: {$status['ose_provider']}\n";
                                                    $statusText .= "📋 Estado General: " . self::getStatusEmoji($status['overall_status']) . " " . self::getStatusText($status['overall_status']) . "\n\n";
                                                    
                                                    $statusText .= "👤 Credenciales: " . ($status['has_credentials'] ? '✅ Disponibles' : '❌ Faltantes') . "\n";
                                                    $statusText .= "🎫 Token Acceso: " . ($status['has_access_token'] ? '✅ Disponible' : '❌ Faltante') . "\n";
                                                    $statusText .= "⏰ Estado Token: " . self::getTokenStatusText($status['token_status']) . "\n";
                                                    $statusText .= "🌐 Endpoint: {$status['endpoint']}\n";
                                                    $statusText .= "🔧 Configurado: " . ($status['is_configured'] ? '✅ Sí' : '❌ No') . "\n";
                                                    
                                                    if (isset($status['token_expires_at'])) {
                                                        $expiresAt = \Carbon\Carbon::parse($status['token_expires_at']);
                                                        $statusText .= "⏳ Expira: " . $expiresAt->format('d/m/Y H:i:s') . "\n";
                                                        
                                                        $hoursUntilExpiry = $status['token_expires_in_hours'];
                                                        if ($hoursUntilExpiry < 0) {
                                                            $statusText .= "🕐 Expiró hace " . abs(round($hoursUntilExpiry, 1)) . " horas\n";
                                                        } else {
                                                            $statusText .= "🕐 Expira en " . round($hoursUntilExpiry, 1) . " horas\n";
                                                        }
                                                    }
                                                    
                                                    if (!empty($status['recommendations'])) {
                                                        $statusText .= "\n💡 Recomendaciones:\n";
                                                        foreach ($status['recommendations'] as $recommendation) {
                                                            $statusText .= "• {$recommendation}\n";
                                                        }
                                                    }
                                                    
                                                    // Determinar tipo de notificación según el estado
                                                    $notificationType = match($status['overall_status']) {
                                                        'fully_configured' => 'success',
                                                        'token_expires_soon' => 'warning',
                                                        'needs_token_refresh' => 'warning',
                                                        'needs_credentials_config' => 'danger',
                                                        default => 'info'
                                                    };
                                                    
                                                    $notification = \Filament\Notifications\Notification::make()
                                                        ->title(__('Estado Completo de QPse'))
                                                        ->body($statusText)
                                                        ->duration(15000);
                                                        
                                                    match($notificationType) {
                                                        'success' => $notification->success(),
                                                        'warning' => $notification->warning(),
                                                        'danger' => $notification->danger(),
                                                        default => $notification->info()
                                                    };
                                                    
                                                    $notification->send();
                                                    
                                                    // Si hay acciones disponibles, mostrar botones adicionales
                                                    if (in_array('refresh_token', $status['actions_available'])) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Acción Disponible'))
                                                            ->body(__('Puede renovar el token de acceso usando el botón "Renovar Token" abajo'))
                                                            ->info()
                                                            ->duration(5000)
                                                            ->send();
                                                    }
                                                }),
                                                
                                            Action::make('refresh_token_only')
                                                ->label(__('🔄 Renovar Solo Token'))
                                                ->icon('heroicon-o-arrow-path')
                                                ->color('warning')
                                                ->visible(fn ($record) => $record->hasQpseCredentials())
                                                ->action(function ($record) {
                                                    $tokenService = app(\App\Services\QpseTokenService::class);
                                                    $result = $tokenService->refreshAccessToken($record);
                                                    
                                                    if ($result['success']) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Token Renovado'))
                                                            ->body($result['message'] . ' (Expira en ' . $result['expires_in_hours'] . ' horas)')
                                                            ->success()
                                                            ->duration(5000)
                                                            ->send();
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Error Renovando Token'))
                                                            ->body($result['error']['message'] ?? __('Error desconocido'))
                                                            ->danger()
                                                            ->duration(8000)
                                                            ->send();
                                                    }
                                                }),
                                                
                                            Action::make('test_connection_only')
                                                ->label(__('📶 Probar Conexión'))
                                                ->icon('heroicon-o-signal')
                                                ->color('info')
                                                ->visible(fn ($record) => $record->hasQpseCredentials() && !empty($record->qpse_access_token))
                                                ->action(function ($record) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Probando conexión...'))
                                                        ->body(__('Verificando conectividad con QPse'))
                                                        ->info()
                                                        ->duration(3000)
                                                        ->send();
                                                    
                                                    $tokenService = app(\App\Services\QpseTokenService::class);
                                                    $result = $tokenService->testConnection($record);
                                                    
                                                    if ($result['success']) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('✅ Conexión Exitosa'))
                                                            ->body($result['message'] . ' (Código: ' . ($result['status_code'] ?? 'N/A') . ')')
                                                            ->success()
                                                            ->duration(5000)
                                                            ->send();
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('❌ Error de Conexión'))
                                                            ->body($result['error']['message'] ?? __('Error desconocido de conexión'))
                                                            ->danger()
                                                            ->duration(8000)
                                                            ->send();
                                                    }
                                                }),
                                        ])->columnSpanFull(),
                                    ]),

                                Section::make(__('🔌 QPse - Configuración'))
                                    ->description(__('Credenciales para el proveedor QPse'))
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->visible(fn ($get) => $get('ose_provider') === 'qpse')
                                    ->schema([
                                        TextInput::make('ose_endpoint')
                                            ->maxLength(255)
                                            ->label(__('Endpoint QPse'))
                                            ->placeholder('https://demo-cpe.qpse.pe')
                                            ->default('https://demo-cpe.qpse.pe')
                                            ->columnSpanFull(),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('ose_username')
                                                    ->maxLength(100)
                                                    ->label(__('Usuario QPse'))
                                                    ->placeholder('usuario_qpse'),
                                                    
                                                TextInput::make('ose_password')
                                                    ->password()
                                                    ->maxLength(100)
                                                    ->dehydrated(fn ($state) => filled($state))
                                                    ->label(__('Contraseña QPse'))
                                                    ->placeholder('••••••••'),
                                            ]),
                                            
                                        Actions::make([
                                            Action::make('test_qpse_connection')
                                                ->label(__('Probar Conexión QPse'))
                                                ->icon('heroicon-o-signal')
                                                ->color('info')
                                                ->action(function ($data, $get) {
                                                    $endpoint = $get('ose_endpoint') ?: 'https://demo-cpe.qpse.pe';
                                                    $username = $get('ose_username');
                                                    $password = $get('ose_password');
                                                    
                                                    if (!$username || !$password) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Datos incompletos'))
                                                            ->body(__('Ingrese usuario y contraseña de QPse'))
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Probando conexión...'))
                                                        ->body(__('Conectando con ') . $endpoint)
                                                        ->info()
                                                        ->send();
                                                    
                                                    $apiService = app(CompanyApiService::class);
                                                    $result = $apiService->testQpseConnection($endpoint, $username, $password);
                                                    
                                                    if ($result['success']) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Conexión exitosa'))
                                                            ->body($result['message'])
                                                            ->success()
                                                            ->duration(5000)
                                                            ->send();
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Error de conexión'))
                                                            ->body($result['error'] ?? __('No se pudo conectar con QPse'))
                                                            ->danger()
                                                            ->duration(8000)
                                                            ->send();
                                                    }
                                                }),
                                        ])->columnSpanFull(),
                                    ]),
                                    
                                Section::make(__('🔌 Otros Proveedores'))
                                    ->description(__('Configuración para otros proveedores OSE'))
                                    ->icon('heroicon-o-puzzle-piece')
                                    ->visible(fn ($get) => !in_array($get('ose_provider'), ['qpse', 'sunat']))
                                    ->schema([
                                        TextInput::make('ose_endpoint')
                                            ->maxLength(255)
                                            ->label(__('URL del Servicio'))
                                            ->url()
                                            ->columnSpanFull(),
                                            
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('ose_username')
                                                    ->maxLength(100)
                                                    ->label(__('Usuario')),
                                                    
                                                TextInput::make('ose_password')
                                                    ->password()
                                                    ->maxLength(100)
                                                    ->dehydrated(fn ($state) => filled($state))
                                                    ->label(__('Contraseña')),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('api_integration')
                            ->label(__('🔗 Integraciones API'))
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                Section::make(__('🏦 API Factiliza'))
                                    ->description(__('Configuración para consultas de RUC y datos empresariales'))
                                    ->icon('heroicon-o-cloud')
                                    ->schema([
                                        TextInput::make('factiliza_token')
                                            ->maxLength(1000)
                                            ->label(__('Token API Factiliza'))
                                            ->placeholder('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...')
                                            ->helperText(__('Token JWT para consultas de API de Factiliza'))
                                            ->columnSpanFull(),
                                            
                                        Actions::make([
                                            Action::make('test_factiliza_api')
                                                ->label(__('Probar API Factiliza'))
                                                ->icon('heroicon-o-beaker')
                                                ->color('success')
                                                ->action(function ($data, $get) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Probando API...'))
                                                        ->body(__('Verificando conexión con Factiliza'))
                                                        ->info()
                                                        ->send();
                                                    
                                                    // Usar FactilizaService para probar con un RUC conocido
                                                    $factilizaService = app(\App\Services\FactilizaService::class);
                                                    
                                                    // Verificar si el token está configurado
                                                    if (!$factilizaService->tokenConfigurado()) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Token no configurado'))
                                                            ->body(__('Configure el token de Factiliza en una empresa activa'))
                                                            ->warning()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    // Probar con RUC de SUNAT (conocido)
                                                    $testRuc = '20131312955';
                                                    $result = $factilizaService->consultarRuc($testRuc);
                                                    
                                                    if ($result['success']) {
                                                        $companyName = $result['data']['nombre_o_razon_social'] ?? 'N/A';
                                                        
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('API funcionando'))
                                                            ->body(__('Conexión exitosa. Test RUC: ') . $companyName)
                                                            ->success()
                                                            ->duration(8000)
                                                            ->send();
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Error en API'))
                                                            ->body($result['message'] ?? __('No se pudo conectar con Factiliza'))
                                                            ->danger()
                                                            ->duration(8000)
                                                            ->send();
                                                    }
                                                }),
                                                
                                            Action::make('consult_own_ruc')
                                                ->label(__('Consultar Mi RUC'))
                                                ->icon('heroicon-o-magnifying-glass')
                                                ->color('info')
                                                ->action(function ($data, $set, $get, $record) {
                                                    $ruc = $get('ruc') ?? $record?->ruc ?? null;
                                                    
                                                    if (!$ruc || strlen($ruc) !== 11 || !is_numeric($ruc)) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('RUC requerido'))
                                                            ->body(__('Debe ingresar un RUC válido de 11 dígitos'))
                                                            ->danger()
                                                            ->send();
                                                        return;
                                                    }
                                                    
                                                    \Filament\Notifications\Notification::make()
                                                        ->title(__('Consultando RUC...'))
                                                        ->body(__('Obteniendo información de: ') . $ruc)
                                                        ->info()
                                                        ->send();
                                                    
                                                    // Usar el mismo patrón que ClientResource
                                                    $factilizaService = app(\App\Services\FactilizaService::class);
                                                    $result = $factilizaService->consultarRuc($ruc);
                                                    
                                                    if ($result['success'] && $result['data']) {
                                                        $data = $result['data'];
                                                        
                                                        // Mostrar datos en notificación
                                                        $details = "Razón Social: " . ($data['nombre_o_razon_social'] ?? 'N/A') . "\n";
                                                        $details .= "Estado: " . ($data['estado'] ?? 'N/A') . "\n";
                                                        $details .= "Condición: " . ($data['condicion'] ?? 'N/A') . "\n";
                                                        $details .= "Dirección: " . ($data['direccion'] ?? 'N/A') . "\n";
                                                        $details .= "Distrito: " . ($data['distrito'] ?? 'N/A') . "\n";
                                                        $details .= "Fuente: Factiliza";
                                                        
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Información del RUC'))
                                                            ->body($details)
                                                            ->success()
                                                            ->duration(10000)
                                                            ->send();
                                                            
                                                        // Actualizar campos automáticamente en el formulario
                                                        if (!$get('business_name') && $data['nombre_o_razon_social']) {
                                                            $set('business_name', $data['nombre_o_razon_social']);
                                                        }
                                                        if (!$get('address') && $data['direccion']) {
                                                            $set('address', $data['direccion']);
                                                        }
                                                        if (!$get('district') && $data['distrito']) {
                                                            $set('district', $data['distrito']);
                                                        }
                                                        if (!$get('province') && $data['provincia']) {
                                                            $set('province', $data['provincia']);
                                                        }
                                                        if (!$get('department') && $data['departamento']) {
                                                            $set('department', $data['departamento']);
                                                        }
                                                        if (!$get('ubigeo') && $data['ubigeo_sunat']) {
                                                            $set('ubigeo', $data['ubigeo_sunat']);
                                                        }
                                                        
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('Campos actualizados'))
                                                            ->body(__('Se llenaron automáticamente los campos vacíos'))
                                                            ->info()
                                                            ->send();
                                                            
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title(__('RUC no encontrado'))
                                                            ->body($result['message'] ?? __('No se encontraron datos para este RUC'))
                                                            ->warning()
                                                            ->duration(8000)
                                                            ->send();
                                                    }
                                                }),
                                        ])->columnSpanFull(),
                                    ]),
                                    
                                Section::make(__('📊 Otras APIs'))
                                    ->description(__('Configuración para otras integraciones de terceros'))
                                    ->icon('heroicon-o-squares-plus')
                                    ->schema([
                                        Placeholder::make('other_apis')
                                            ->label(__('APIs Disponibles'))
                                            ->content(__('• API SUNAT (Consulta RUC)') . "\n" . 
                                                     __('• API Reniec (Consulta DNI)') . "\n" .
                                                     __('• API Tipo de Cambio (SBS)') . "\n" .
                                                     __('• APIs personalizadas'))
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ruc')
                    ->searchable()
                    ->sortable()
                    ->label(__('RUC')),
                    
                TextColumn::make('business_name')
                    ->searchable()
                    ->sortable()
                    ->label(__('Razón Social')),
                    
                TextColumn::make('commercial_name')
                    ->searchable()
                    ->label(__('Nombre Comercial')),
                    
                TextColumn::make('address')
                    ->label(__('Dirección')),
                    
                TextColumn::make('phone')
                    ->label(__('Teléfono')),
                    
                TextColumn::make('email')
                    ->label(__('Email')),
                    
                TextColumn::make('factiliza_token')
                    ->label(__('API Factiliza'))
                    ->formatStateUsing(fn ($state) => $state ? '✅ Configurado' : '❌ No configurado')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                    
                ToggleColumn::make('status')
                    ->label(__('Activo')),
            ])
            ->filters([
                SelectFilter::make('tax_regime')
                    ->options([
                        'general' => __('Régimen General'),
                        'special' => __('Régimen Especial'),
                        'mype' => __('Régimen MYPE Tributario'),
                        'simplified' => __('Régimen Simplificado'),
                    ])
                    ->label(__('Régimen Tributario')),
                    
                SelectFilter::make('status')
                    ->options([
                        '1' => __('Activo'),
                        '0' => __('Inactivo'),
                    ])
                    ->label(__('Estado')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
            'view' => Pages\ViewCompany::route('/{record}'),
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
     * Helper methods for QPse status display
     */
    private static function getStatusEmoji(string $status): string
    {
        return match($status) {
            'fully_configured' => '✅',
            'token_expires_soon' => '⚠️',
            'needs_token_refresh' => '🔄',
            'needs_credentials_config' => '⚙️',
            default => '❓'
        };
    }

    private static function getStatusText(string $status): string
    {
        return match($status) {
            'fully_configured' => __('Completamente Configurado'),
            'token_expires_soon' => __('Token Expira Pronto'),
            'needs_token_refresh' => __('Necesita Renovar Token'),
            'needs_credentials_config' => __('Necesita Configurar Credenciales'),
            default => __('Estado Desconocido')
        };
    }

    private static function getTokenStatusText(string $tokenStatus): string
    {
        return match($tokenStatus) {
            'valid' => '✅ ' . __('Válido'),
            'expires_soon' => '⚠️ ' . __('Expira Pronto'),
            'expired' => '❌ ' . __('Expirado'),
            'no_token' => '❌ ' . __('Sin Token'),
            'unknown_expiration' => '❓ ' . __('Expiración Desconocida'),
            default => '❓ ' . __('Estado Desconocido')
        };
    }
}