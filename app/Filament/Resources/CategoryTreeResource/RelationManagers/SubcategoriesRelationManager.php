<?php

namespace App\Filament\Resources\CategoryTreeResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $title = 'Subcategorías';
    
    public static function getModelLabel(): string
    {
        return __('Subcategoría');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Subcategorías');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-document')
                    ->label(__('Nombre')),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->label(__('Slug')),

                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->label(__('Orden')),

                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->badge()
                    ->color('info')
                    ->label(__('Productos')),

                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label(__('Estado')),

                Tables\Columns\ColorColumn::make('color')
                    ->placeholder(__('Sin color'))
                    ->label(__('Color')),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\TernaryFilter::make('status')
                    ->label(__('Estado'))
                    ->placeholder(__('Todas'))
                    ->trueLabel(__('Solo activas'))
                    ->falseLabel(__('Solo inactivas')),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
