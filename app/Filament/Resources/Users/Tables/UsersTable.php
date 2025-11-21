<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nombre'))
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('email')
                    ->label(__('Correo Electrónico'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('Correo copiado'))
                    ->copyMessageDuration(1500),
                    
                TextColumn::make('roles.name')
                    ->badge()
                    ->label(__('Roles'))
                    ->colors([
                        'primary',
                    ])
                    ->placeholder(__('Sin roles asignados')),
                    
                TextColumn::make('email_verified_at')
                    ->label(__('Email Verificado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->placeholder(__('No verificado')),
                    
                TextColumn::make('created_at')
                    ->label(__('Creado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label(__('Actualizado'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label(__('Ver')),
                EditAction::make()->label(__('Editar')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label(__('Eliminar seleccionados')),
                ]),
            ]);
    }
}
