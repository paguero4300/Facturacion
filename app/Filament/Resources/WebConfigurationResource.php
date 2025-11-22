<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebConfigurationResource\Pages;
use App\Models\WebConfiguration;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use BackedEnum;
use UnitEnum;

class WebConfigurationResource extends Resource
{
    protected static ?string $model = WebConfiguration::class;

    protected static ?string $navigationLabel = 'Configuración Web';

    protected static ?string $modelLabel = 'Configuración Web';

    protected static ?string $pluralModelLabel = 'Configuraciones Web';

    protected static string|UnitEnum|null $navigationGroup = 'Administración';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Forms\Components\Select::make('company_id')
                    ->relationship('company', 'commercial_name')
                    ->required()
                    ->label('Empresa')
                    ->columnSpanFull()
                    ->default(function () {
                        $companyId = request()->get('company_id');
                        if ($companyId) {
                            return $companyId;
                        }
                        return null;
                    }),
                
                \Filament\Schemas\Components\Section::make('Información de Contacto')
                    ->description('Configura los datos principales de contacto para tus clientes')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->placeholder('Ej: contacto@empresa.com')
                            ->required()
                            ->prefixIcon('heroicon-o-envelope')
                            ->columnSpanFull(),
                        
                        \Filament\Forms\Components\TextInput::make('horario_atencion')
                            ->label('Horario de Atención')
                            ->placeholder('Ej: Lun - Dom: 9:00 - 20:00')
                            ->required()
                            ->prefixIcon('heroicon-o-clock')
                            ->columnSpanFull(),
                        
                        \Filament\Forms\Components\TextInput::make('telefono_huancayo')
                            ->label('Teléfono 1')
                            ->placeholder('Ej: (+51) 944 492 316')
                            ->required()
                            ->prefixIcon('heroicon-o-phone'),
                        
                        \Filament\Forms\Components\TextInput::make('telefono_lima')
                            ->label('Teléfono 2')
                            ->placeholder('Ej: (+51) 944 492 317')
                            ->required()
                            ->prefixIcon('heroicon-o-phone'),
                    ])
                    ->columns(2),
                
                \Filament\Schemas\Components\Section::make('Redes Sociales')
                    ->description('Enlaza tus perfiles de redes sociales para que los clientes puedan seguirte')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('facebook')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/tu-pagina')
                            ->required()
                            ->prefixIcon('heroicon-o-globe-alt'),
                        
                        \Filament\Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/tu-perfil')
                            ->required()
                            ->prefixIcon('heroicon-o-camera'),
                        
                        \Filament\Forms\Components\TextInput::make('tiktok')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@tu-usuario')
                            ->required()
                            ->prefixIcon('heroicon-o-video-camera'),
                    ])
                    ->columns(1),
                 
                 \Filament\Schemas\Components\Section::make('Banners del Sitio Web')
                     ->description('Configura los banners para el carrusel principal. Puedes usar imágenes o videos. Si solo cargas un banner, se mostrará estático. Si cargas varios, se mostrarán como carrusel.')
                     ->schema([
                         // Banner 1
                         \Filament\Schemas\Components\Section::make('Banner 1')
                             ->schema([
                                 \Filament\Forms\Components\Radio::make('banner_1_type')
                                     ->label('Tipo de Contenido')
                                     ->options([
                                         'image' => 'Imagen',
                                         'video' => 'Video',
                                     ])
                                     ->default('image')
                                     ->inline()
                                     ->reactive()
                                     ->required()
                                     ->columnSpanFull(),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_1_imagen')
                                     ->label('Imagen del Banner 1')
                                     ->image()
                                     ->imageEditor()
                                     ->directory('banners/images')
                                     ->disk('public')
                                     ->maxSize(2048)
                                     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                     ->helperText('Dimensiones recomendadas: 1920x1080px (Full HD) o 1200x600px')
                                     ->hidden(fn ($get) => $get('banner_1_type') === 'video'),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_1_video')
                                     ->label('Video del Banner 1')
                                     ->directory('banners/videos')
                                     ->disk('public')
                                     ->maxSize(51200) // 50MB
                                     ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                     ->helperText('Formatos aceptados: MP4, WebM, MOV. Tamaño máximo: 50MB. Recomendación: videos cortos (5-15 segundos) en resolución 1920x1080px para mejor rendimiento.')
                                     ->hidden(fn ($get) => $get('banner_1_type') !== 'video'),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_1_titulo')
                                     ->label('Título del Banner 1')
                                     ->placeholder('Ej: Ofertas Especiales')
                                     ->maxLength(100),
                                 
                                 \Filament\Forms\Components\Textarea::make('banner_1_texto')
                                     ->label('Texto del Banner 1')
                                     ->placeholder('Ej: Descubre nuestras promociones de temporada')
                                     ->rows(2)
                                     ->maxLength(200),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_1_link')
                                     ->label('Enlace del Banner 1')
                                     ->url()
                                     ->placeholder('https://ejemplo.com')
                                     ->prefixIcon('heroicon-o-link'),
                             ])
                             ->columns(2)
                             ->collapsible()
                             ->collapsed(fn ($get) => !$get('banner_1_imagen') && !$get('banner_1_video')),
                         
                         // Banner 2
                         \Filament\Schemas\Components\Section::make('Banner 2')
                             ->schema([
                                 \Filament\Forms\Components\Radio::make('banner_2_type')
                                     ->label('Tipo de Contenido')
                                     ->options([
                                         'image' => 'Imagen',
                                         'video' => 'Video',
                                     ])
                                     ->default('image')
                                     ->inline()
                                     ->reactive()
                                     ->required()
                                     ->columnSpanFull(),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_2_imagen')
                                     ->label('Imagen del Banner 2')
                                     ->image()
                                     ->imageEditor()
                                     ->directory('banners/images')
                                     ->disk('public')
                                     ->maxSize(2048)
                                     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                     ->helperText('Dimensiones recomendadas: 1920x1080px (Full HD) o 1200x600px')
                                     ->hidden(fn ($get) => $get('banner_2_type') === 'video'),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_2_video')
                                     ->label('Video del Banner 2')
                                     ->directory('banners/videos')
                                     ->disk('public')
                                     ->maxSize(51200) // 50MB
                                     ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                     ->helperText('Formatos aceptados: MP4, WebM, MOV. Tamaño máximo: 50MB. Recomendación: videos cortos (5-15 segundos) en resolución 1920x1080px para mejor rendimiento.')
                                     ->hidden(fn ($get) => $get('banner_2_type') !== 'video'),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_2_titulo')
                                     ->label('Título del Banner 2')
                                     ->placeholder('Ej: Nuevos Productos')
                                     ->maxLength(100),
                                 
                                 \Filament\Forms\Components\Textarea::make('banner_2_texto')
                                     ->label('Texto del Banner 2')
                                     ->placeholder('Ej: Conoce las últimas novedades')
                                     ->rows(2)
                                     ->maxLength(200),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_2_link')
                                     ->label('Enlace del Banner 2')
                                     ->url()
                                     ->placeholder('https://ejemplo.com')
                                     ->prefixIcon('heroicon-o-link'),
                             ])
                             ->columns(2)
                             ->collapsible()
                             ->collapsed(fn ($get) => !$get('banner_2_imagen') && !$get('banner_2_video')),
                         
                         // Banner 3
                         \Filament\Schemas\Components\Section::make('Banner 3')
                             ->schema([
                                 \Filament\Forms\Components\Radio::make('banner_3_type')
                                     ->label('Tipo de Contenido')
                                     ->options([
                                         'image' => 'Imagen',
                                         'video' => 'Video',
                                     ])
                                     ->default('image')
                                     ->inline()
                                     ->reactive()
                                     ->required()
                                     ->columnSpanFull(),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_3_imagen')
                                     ->label('Imagen del Banner 3')
                                     ->image()
                                     ->imageEditor()
                                     ->directory('banners/images')
                                     ->disk('public')
                                     ->maxSize(2048)
                                     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                     ->helperText('Dimensiones recomendadas: 1920x1080px (Full HD) o 1200x600px')
                                     ->hidden(fn ($get) => $get('banner_3_type') === 'video'),
                                 
                                 \Filament\Forms\Components\FileUpload::make('banner_3_video')
                                     ->label('Video del Banner 3')
                                     ->directory('banners/videos')
                                     ->disk('public')
                                     ->maxSize(51200) // 50MB
                                     ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/quicktime'])
                                     ->helperText('Formatos aceptados: MP4, WebM, MOV. Tamaño máximo: 50MB. Recomendación: videos cortos (5-15 segundos) en resolución 1920x1080px para mejor rendimiento.')
                                     ->hidden(fn ($get) => $get('banner_3_type') !== 'video'),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_3_titulo')
                                     ->label('Título del Banner 3')
                                     ->placeholder('Ej: Servicios Premium')
                                     ->maxLength(100),
                                 
                                 \Filament\Forms\Components\Textarea::make('banner_3_texto')
                                     ->label('Texto del Banner 3')
                                     ->placeholder('Ej: Calidad garantizada en todos nuestros productos')
                                     ->rows(2)
                                     ->maxLength(200),
                                 
                                 \Filament\Forms\Components\TextInput::make('banner_3_link')
                                     ->label('Enlace del Banner 3')
                                     ->url()
                                     ->placeholder('https://ejemplo.com')
                                     ->prefixIcon('heroicon-o-link'),
                             ])
                             ->columns(2)
                             ->collapsible()
                             ->collapsed(fn ($get) => !$get('banner_3_imagen') && !$get('banner_3_video')),
                     ])
                     ->columns(1)
                     ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.commercial_name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('telefono_huancayo')
                    ->label('Teléfono 1')
                    ->searchable(),
                
                TextColumn::make('telefono_lima')
                    ->label('Teléfono 2')
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                
                TextColumn::make('horario_atencion')
                    ->label('Horario')
                    ->limit(30),
                
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListWebConfigurations::route('/'),
            'create' => Pages\CreateWebConfiguration::route('/create'),
            'edit' => Pages\EditWebConfiguration::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        // Filtrar por company_id si está presente en la URL
        $companyId = request()->get('company_id');
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query;
    }
}