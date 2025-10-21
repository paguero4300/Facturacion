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
                            ->label('Teléfono Huancayo')
                            ->placeholder('Ej: (+51) 944 492 316')
                            ->required()
                            ->prefixIcon('heroicon-o-phone'),
                        
                        \Filament\Forms\Components\TextInput::make('telefono_lima')
                            ->label('Teléfono Lima')
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
                    ->label('Teléfono Huancayo')
                    ->searchable(),
                
                TextColumn::make('telefono_lima')
                    ->label('Teléfono Lima')
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