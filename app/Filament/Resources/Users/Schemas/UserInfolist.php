<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('Nombre')),
                    
                TextEntry::make('email')
                    ->label(__('Correo Electrónico')),
                    
                TextEntry::make('roles.name')
                    ->badge()
                    ->label(__('Roles'))
                    ->colors([
                        'primary',
                    ])
                    ->placeholder(__('Sin roles asignados')),
                    
                TextEntry::make('email_verified_at')
                    ->label(__('Email Verificado'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder(__('No verificado')),
                    
                TextEntry::make('created_at')
                    ->label(__('Creado'))
                    ->dateTime('d/m/Y H:i'),
                    
                TextEntry::make('updated_at')
                    ->label(__('Actualizado'))
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
