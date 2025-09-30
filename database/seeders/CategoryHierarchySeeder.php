<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryHierarchySeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Nombres de las 4 categorías principales
        $mainCategories = [
            'Ocasiones',
            'Arreglos',
            'Regalos',
            'Festivos',
        ];

        // Obtener todas las categorías existentes (sin parent_id asignado)
        $existingCategories = Category::whereNull('parent_id')->get();

        if ($existingCategories->isEmpty()) {
            $this->command->info('No hay categorías existentes para agrupar.');
            return;
        }

        // Obtener la primera categoría para usar su company_id y created_by
        $firstCategory = $existingCategories->first();
        $companyId = $firstCategory->company_id;
        $createdBy = $firstCategory->created_by;

        $this->command->info("Encontradas {$existingCategories->count()} categorías para agrupar...");

        // Crear las 4 categorías principales
        $parentCategories = [];
        foreach ($mainCategories as $index => $name) {
            $parent = Category::create([
                'company_id' => $companyId,
                'parent_id' => null,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Categoría principal: {$name}",
                'order' => ($index + 1) * 10, // 10, 20, 30, 40
                'status' => true,
                'created_by' => $createdBy,
            ]);

            $parentCategories[] = $parent;
            $this->command->info("✓ Creada categoría principal: {$name}");
        }

        // Distribuir las categorías existentes aleatoriamente entre las 4 principales
        $shuffledCategories = $existingCategories->shuffle();
        $categoriesPerParent = ceil($existingCategories->count() / 4);

        $chunks = $shuffledCategories->chunk($categoriesPerParent);

        foreach ($chunks as $chunkIndex => $chunk) {
            $parentCategory = $parentCategories[$chunkIndex] ?? $parentCategories[0];
            
            foreach ($chunk as $orderIndex => $category) {
                // Actualizar la categoría existente para asignarle un padre
                $category->update([
                    'parent_id' => $parentCategory->id,
                    'slug' => $category->slug ?? Str::slug($category->name),
                    'order' => ($orderIndex + 1) * 10,
                ]);

                $this->command->info("  → {$category->name} ahora es subcategoría de {$parentCategory->name}");
            }
        }

        $this->command->info("\n✅ Proceso completado!");
        $this->command->info("Se crearon 4 categorías principales y se agruparon {$existingCategories->count()} subcategorías.");
        
        // Mostrar resumen
        $this->command->info("\n📊 Resumen:");
        foreach ($parentCategories as $parent) {
            $childrenCount = $parent->children()->count();
            $this->command->info("  • {$parent->name}: {$childrenCount} subcategorías");
        }
    }
}
