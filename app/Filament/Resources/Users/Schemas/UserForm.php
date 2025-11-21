<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Nombre'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('Nombre completo del usuario')),
                    
                TextInput::make('email')
                    ->label(__('Correo Electrónico'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder(__('usuario@ejemplo.com')),
                    
                TextInput::make('password')
                    ->label(__('Contraseña'))
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->placeholder(__('Mínimo 8 caracteres'))
                    ->helperText(__('Dejar vacío para mantener la contraseña actual')),
                
                // Campo para asignar roles
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->label(__('Roles'))
                    ->placeholder(__('Seleccionar roles'))
                    ->helperText(__('Asigna uno o más roles al usuario')),
                    
                DateTimePicker::make('email_verified_at')
                    ->label(__('Email Verificado'))
                    ->helperText(__('Fecha y hora de verificación del correo')),
            ]);
    }
}
