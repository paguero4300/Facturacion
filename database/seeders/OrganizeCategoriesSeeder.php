<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class OrganizeCategoriesSeeder extends Seeder
{
    /**
     * Organiza las categorías existentes:
     * - Crea categorías principales si no existen
     * - Convierte el resto en subcategorías
     */
    public function run(): void
    {
        $this->command->info('🔍 Analizando categorías existentes...');

        // Obtener todas las categorías actuales
        $allCategories = Category::whereNull('parent_id')->get();
        $this->command->info("Encontradas {$allCategories->count()} categorías sin padre");

        if ($allCategories->isEmpty()) {
            $this->command->error('❌ No hay categorías para organizar');
            return;
        }

        // Obtener datos de la primera categoría para company_id y created_by
        $firstCategory = $allCategories->first();
        $companyId = $firstCategory->company_id;
        $createdBy = $firstCategory->created_by;

        // Definir las categorías principales deseadas
        $mainCategoryNames = ['Ocasiones', 'Arreglos', 'Regalos', 'Festivos'];
        $mainCategories = [];

        // Buscar o crear las categorías principales
        foreach ($mainCategoryNames as $index => $name) {
            $category = Category::where('name', $name)
                ->where('company_id', $companyId)
                ->first();

            if (!$category) {
                // Crear la categoría principal si no existe
                $category = Category::create([
                    'company_id' => $companyId,
                    'parent_id' => null,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "Categoría principal: {$name}",
                    'order' => ($index + 1) * 10,
                    'status' => true,
                    'created_by' => $createdBy,
                ]);
                $this->command->info("✅ Creada categoría principal: {$name}");
            } else {
                $this->command->info("ℹ️  Ya existe categoría principal: {$name}");
            }

            $mainCategories[] = $category;
        }

        // Obtener todas las categorías que NO son principales
        $categoriesToConvert = Category::whereNull('parent_id')
            ->whereNotIn('id', collect($mainCategories)->pluck('id'))
            ->get();

        $this->command->info("\n📦 Convirtiendo {$categoriesToConvert->count()} categorías en subcategorías...\n");

        // Convertir el resto en subcategorías distribuyéndolas entre las principales
        foreach ($categoriesToConvert as $index => $category) {
            // Asignar a una categoría principal de forma cíclica
            $parentIndex = $index % count($mainCategories);
            $parentCategory = $mainCategories[$parentIndex];

            $category->update([
                'parent_id' => $parentCategory->id,
                'slug' => $category->slug ?? Str::slug($category->name),
                'order' => ($index % 10 + 1) * 10,
            ]);

            $this->command->info("  → {$category->name} ahora es subcategoría de {$parentCategory->name}");
        }

        // Mostrar resumen
        $this->command->info("\n✅ Proceso completado!\n");
        $this->command->info("📊 RESUMEN:");
        foreach ($mainCategories as $parent) {
            $childrenCount = $parent->children()->count();
            $this->command->info("  📁 {$parent->name}: {$childrenCount} subcategorías");
            
            // Mostrar las subcategorías
            $children = $parent->children()->orderBy('order')->get();
            foreach ($children as $child) {
                $this->command->line("     ↳ {$child->name}");
            }
        }

        $this->command->info("\n🎉 ¡Ahora tienes las categorías organizadas correctamente!");
    }
}
