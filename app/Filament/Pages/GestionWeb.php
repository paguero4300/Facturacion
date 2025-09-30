<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Company;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GestionWeb extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Gestión Web';
    protected static ?string $title = 'Gestión de Contenido Web';
    protected string $view = 'filament.pages.gestion-web';

    public ?array $data = [];

    // Propiedades para almacenar las categorías
    public $mainCategories = [];
    public $regularCategories = [];

    // Datos para el formulario
    public $main_category_name = '';
    public $main_category_description = '';
    public $main_category_color = '#ff9900';
    public $main_category_icon = 'heroicon-o-folder';
    public $main_category_status = true;
    public $show_on_web = true;
    
    // Datos para las categorías principales
    public $categories = [];
    public $new_categories = [];

    // Datos para las categorías secundarias
    public $secondary_categories = [];
    public $new_secondary_categories = [];

    // Datos para las categorías especiales
    public $special_categories = [];
    public $new_special_categories = [];

    // Grupos disponibles
    public $available_groups = [
        'principales' => 'Categorías Principales',
        'secundarias' => 'Categorías Secundarias',
        'especiales' => 'Categorías Especiales',
    ];

    public function mount(): void
    {
        $this->loadCategories();
        $this->loadMainCategories();
        $this->loadRegularCategories();
    }
    
    protected function loadMainCategories(): void
    {
        try {
            $this->mainCategories = Category::where('is_main_category', true)
                ->orderBy('web_order')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading main categories: ' . $e->getMessage());
            $this->mainCategories = [];
        }
    }
    
    protected function loadRegularCategories(): void
    {
        try {
            $this->regularCategories = Category::where('is_main_category', false)
                ->whereNull('main_category_id')
                ->orderBy('web_order')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading regular categories: ' . $e->getMessage());
            $this->regularCategories = [];
        }
    }

    protected function loadCategories(): void
    {
        try {
            // Cargar categorías principales
            $this->categories = Category::where('web_group', 'principales')
                ->where('show_on_web', true)
                ->orderBy('web_order')
                ->get()
                ->toArray();

            // Cargar categorías secundarias
            $this->secondary_categories = Category::where('web_group', 'secundarias')
                ->where('show_on_web', true)
                ->orderBy('web_order')
                ->get()
                ->toArray();

            // Cargar categorías especiales
            $this->special_categories = Category::where('web_group', 'especiales')
                ->orderBy('web_order')
                ->get()
                ->toArray();

            // Obtener la categoría principal si existe
            $mainCategory = Category::where('is_main_category', true)->first();
            if ($mainCategory) {
                $this->main_category_name = $mainCategory->name;
                $this->main_category_description = $mainCategory->description;
                $this->main_category_color = $mainCategory->color;
                $this->main_category_icon = $mainCategory->icon;
                $this->main_category_status = (bool)$mainCategory->status;
                $this->show_on_web = (bool)$mainCategory->show_on_web;
            }
        } catch (\Exception $e) {
            Log::error('Error loading categories: ' . $e->getMessage());
            Notification::make()
                ->title('Error al cargar categorías')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Sección para la categoría principal
                Section::make('Categoría Principal')
                    ->description('Configuración de la categoría principal para la web')
                    ->schema([
                        TextInput::make('main_category_name')
                            ->label('Nombre de la Categoría Principal')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('main_category_description')
                            ->label('Descripción')
                            ->maxLength(500),
                        TextInput::make('main_category_color')
                            ->label('Color')
                            ->type('color')
                            ->default('#ff9900'),
                        TextInput::make('main_category_icon')
                            ->label('Icono')
                            ->default('heroicon-o-folder'),
                        Toggle::make('main_category_status')
                            ->label('Estado Activo')
                            ->default(true),
                        Toggle::make('show_on_web')
                            ->label('Mostrar en la Web')
                            ->default(true),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function addNewCategory($group)
    {
        $newCategory = [
            'id' => 'new_' . uniqid(),
            'name' => '',
            'description' => '',
            'color' => '#ff9900',
            'icon' => 'heroicon-o-cube',
            'status' => true,
            'show_on_web' => true,
            'web_order' => 1,
            'web_group' => $group,
            'is_new' => true,
        ];

        if ($group === 'principales') {
            $this->new_categories[] = $newCategory;
        } elseif ($group === 'secundarias') {
            $this->new_secondary_categories[] = $newCategory;
        } elseif ($group === 'especiales') {
            $this->new_special_categories[] = $newCategory;
        }
    }

    public function removeNewCategory($group, $index)
    {
        if ($group === 'principales') {
            unset($this->new_categories[$index]);
            $this->new_categories = array_values($this->new_categories);
        } elseif ($group === 'secundarias') {
            unset($this->new_secondary_categories[$index]);
            $this->new_secondary_categories = array_values($this->new_secondary_categories);
        } elseif ($group === 'especiales') {
            unset($this->new_special_categories[$index]);
            $this->new_special_categories = array_values($this->new_special_categories);
        }
    }

    public function save()
    {
        try {
            DB::beginTransaction();

            $company = Company::first();
            if (!$company) {
                throw new \Exception('No se encontró ninguna empresa');
            }

            // Guardar o actualizar la categoría principal
            $mainCategory = Category::where('is_main_category', true)->first();
            
            if (!$mainCategory) {
                $mainCategory = new Category();
                $mainCategory->company_id = $company->id;
                $mainCategory->is_main_category = true;
                $mainCategory->main_category_id = null;
                $mainCategory->web_group = 'principales';
                $mainCategory->web_order = 0;
                $mainCategory->created_by = auth()->id();
            }

            $mainCategory->name = $this->main_category_name;
            $mainCategory->description = $this->main_category_description;
            $mainCategory->color = $this->main_category_color;
            $mainCategory->icon = $this->main_category_icon;
            $mainCategory->status = $this->main_category_status;
            $mainCategory->show_on_web = $this->show_on_web;
            $mainCategory->updated_by = auth()->id();
            $mainCategory->save();

            // Guardar nuevas categorías principales
            foreach ($this->new_categories as $index => $category) {
                if (!empty($category['name'])) {
                    $newCategory = new Category();
                    $newCategory->company_id = $company->id;
                    $newCategory->name = $category['name'];
                    $newCategory->description = $category['description'];
                    $newCategory->color = $category['color'];
                    $newCategory->icon = $category['icon'];
                    $newCategory->status = $category['status'];
                    $newCategory->show_on_web = $category['show_on_web'];
                    $newCategory->web_order = $category['web_order'];
                    $newCategory->web_group = 'principales';
                    $newCategory->is_main_category = false;
                    $newCategory->main_category_id = $mainCategory->id;
                    $newCategory->created_by = auth()->id();
                    $newCategory->save();
                }
            }

            // Guardar nuevas categorías secundarias
            foreach ($this->new_secondary_categories as $index => $category) {
                if (!empty($category['name'])) {
                    $newCategory = new Category();
                    $newCategory->company_id = $company->id;
                    $newCategory->name = $category['name'];
                    $newCategory->description = $category['description'];
                    $newCategory->color = $category['color'];
                    $newCategory->icon = $category['icon'];
                    $newCategory->status = $category['status'];
                    $newCategory->show_on_web = $category['show_on_web'];
                    $newCategory->web_order = $category['web_order'];
                    $newCategory->web_group = 'secundarias';
                    $newCategory->is_main_category = false;
                    $newCategory->main_category_id = $mainCategory->id;
                    $newCategory->created_by = auth()->id();
                    $newCategory->save();
                }
            }

            // Guardar nuevas categorías especiales
            foreach ($this->new_special_categories as $index => $category) {
                if (!empty($category['name'])) {
                    $newCategory = new Category();
                    $newCategory->company_id = $company->id;
                    $newCategory->name = $category['name'];
                    $newCategory->description = $category['description'];
                    $newCategory->color = $category['color'];
                    $newCategory->icon = $category['icon'];
                    $newCategory->status = $category['status'];
                    $newCategory->show_on_web = $category['show_on_web'];
                    $newCategory->web_order = $category['web_order'];
                    $newCategory->web_group = 'especiales';
                    $newCategory->is_main_category = false;
                    $newCategory->main_category_id = $mainCategory->id;
                    $newCategory->created_by = auth()->id();
                    $newCategory->save();
                }
            }

            DB::commit();

            Notification::make()
                ->title('Configuración guardada')
                ->body('La configuración web se ha guardado correctamente')
                ->success()
                ->send();

            // Recargar las categorías
            $this->loadCategories();
            $this->new_categories = [];
            $this->new_secondary_categories = [];
            $this->new_special_categories = [];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving web configuration: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al guardar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function create(): void
    {
        try {
            $company = Company::first();
            if (!$company) {
                throw new \Exception('No se encontró ninguna empresa');
            }
            
            // Crear una categoría principal
            $mainCategory = new Category();
            $mainCategory->company_id = $company->id;
            $mainCategory->name = $this->main_category_name;
            $mainCategory->description = $this->main_category_description;
            $mainCategory->color = $this->main_category_color;
            $mainCategory->icon = $this->main_category_icon;
            $mainCategory->status = $this->main_category_status;
            $mainCategory->show_on_web = $this->show_on_web;
            $mainCategory->web_order = 1;
            $mainCategory->web_group = 'principales';
            $mainCategory->is_main_category = true;
            $mainCategory->main_category_id = null;
            $mainCategory->created_by = auth()->id();
            $mainCategory->save();
            
            Notification::make()
                ->title('Categoría principal creada')
                ->body('La categoría principal se ha creado correctamente')
                ->success()
                ->send();
                
            // Recargar las categorías
            $this->loadCategories();
            $this->loadMainCategories();
            $this->loadRegularCategories();
                
        } catch (\Exception $e) {
            Log::error('Error creating main category: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al crear categoría principal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function assignToMainCategory($categoryId, $mainCategoryId): void
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }
            
            $category->main_category_id = $mainCategoryId;
            $category->save();
            
            Notification::make()
                ->title('Categoría asignada')
                ->body('La categoría se ha asignado correctamente')
                ->success()
                ->send();
                
            // Recargar las categorías
            $this->loadCategories();
            $this->loadMainCategories();
            $this->loadRegularCategories();
                
        } catch (\Exception $e) {
            Log::error('Error assigning category to main category: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al asignar categoría')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public function removeFromMainCategory($categoryId): void
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }
            
            $category->main_category_id = null;
            $category->save();
            
            Notification::make()
                ->title('Categoría quitada')
                ->body('La categoría se ha quitado correctamente del grupo principal')
                ->success()
                ->send();
                
            // Recargar las categorías
            $this->loadCategories();
            $this->loadMainCategories();
            $this->loadRegularCategories();
                
        } catch (\Exception $e) {
            Log::error('Error removing category from main category: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al quitar categoría')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    // Función auxiliar para convertir color hex a RGB
    public function hexToRgb($hex)
    {
        $hex = str_replace("#", "", $hex);
        
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        
        return "$r, $g, $b";
    }

    public function updateCategory($id, $data)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }

            $category->update($data);

            Notification::make()
                ->title('Categoría actualizada')
                ->body('La categoría se ha actualizado correctamente')
                ->success()
                ->send();

            $this->loadCategories();
        } catch (\Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al actualizar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteCategory($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }

            $category->delete();

            Notification::make()
                ->title('Categoría eliminada')
                ->body('La categoría se ha eliminado correctamente')
                ->success()
                ->send();

            $this->loadCategories();
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al eliminar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function toggleCategoryVisibility($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                throw new \Exception('Categoría no encontrada');
            }

            $category->show_on_web = !$category->show_on_web;
            $category->save();

            Notification::make()
                ->title('Visibilidad actualizada')
                ->body('La visibilidad de la categoría se ha actualizado correctamente')
                ->success()
                ->send();

            $this->loadCategories();
        } catch (\Exception $e) {
            Log::error('Error toggling category visibility: ' . $e->getMessage());
            
            Notification::make()
                ->title('Error al actualizar visibilidad')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}