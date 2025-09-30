<?php

namespace App\Filament\Resources\CategoryTreeResource\Pages;

use App\Filament\Resources\CategoryTreeResource;
use App\Models\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ListCategoryTrees extends ListRecords
{
    protected static string $resource = CategoryTreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('Nueva Categoría Principal'))
                ->icon('heroicon-o-folder-plus')
                ->color('success'),
        ];
    }

    protected function getTableQuery(): Builder
    {
        // Mostrar SOLO las categorías principales (sin padre)
        return Category::query()
            ->withoutGlobalScopes()
            ->whereNull('parent_id')
            ->with(['children', 'products'])
            ->orderBy('order');
    }
}
