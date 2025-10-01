@props([
    'categories' => collect(),
    'warehouses' => collect(),
    'activeFilters' => [],
    'filterBreadcrumbs' => [],
    'clearFiltersUrl' => '',
    'showAsHeader' => false,
    'showAsSidebar' => false,
    'showAsDropdowns' => false,
])

@if($showAsHeader)
    <!-- Header Filters for main page -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Filter Controls -->
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <!-- Category Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select id="category-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ isset($activeFilters['category']) && $activeFilters['category']['slug'] === $category->slug ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Warehouse Filter -->
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Almacén</label>
                    <select id="warehouse-filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">Todos los almacenes</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ isset($activeFilters['warehouse']) && $activeFilters['warehouse']['id'] == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}{{ $warehouse->is_default ? ' (Principal)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Clear Filters Button -->
            @if(count($activeFilters) > 0)
                <div class="flex-shrink-0">
                    <a href="{{ $clearFiltersUrl }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar Filtros
                    </a>
                </div>
            @endif
        </div>

        <!-- Active Filters Breadcrumbs -->
        @if(count($filterBreadcrumbs) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm text-gray-600">Filtros activos:</span>
                    @foreach($filterBreadcrumbs as $breadcrumb)
                        <span class="inline-flex items-center px-3 py-1 text-sm bg-pink-100 text-pink-800 rounded-full">
                            {{ $breadcrumb['label'] }}
                            <a href="{{ $breadcrumb['remove_url'] }}" class="ml-2 text-pink-600 hover:text-pink-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </a>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif

@if($showAsSidebar)
    <!-- Sidebar Filters for shop page -->
    <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
        <h3 class="text-lg font-bold mb-4" style="color: var(--enlaces-titulos);">Filtros</h3>
        
        <!-- Categories Section -->
        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">Categorías</h4>
            <ul class="space-y-2">
                <li>
                    <a href="{{ request()->url() }}{{ request()->has('warehouse') ? '?warehouse=' . request()->warehouse : '' }}" 
                       class="block px-3 py-2 rounded-lg transition {{ !request('category') ? 'bg-pink-50 text-pink-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        Todas las categorías
                    </a>
                </li>
                @foreach($categories as $category)
                    <li>
                        @php
                            $categoryParams = ['category' => $category->slug];
                            if (request()->has('warehouse')) {
                                $categoryParams['warehouse'] = request()->warehouse;
                            }
                        @endphp
                        <a href="{{ request()->url() }}?{{ http_build_query($categoryParams) }}" 
                           class="block px-3 py-2 rounded-lg transition {{ request('category') == $category->slug ? 'bg-pink-50 text-pink-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Warehouses Section -->
        <div class="mb-6">
            <h4 class="font-semibold text-gray-800 mb-3">Almacenes</h4>
            <ul class="space-y-2">
                <li>
                    <a href="{{ request()->url() }}{{ request()->has('category') ? '?category=' . request()->category : '' }}" 
                       class="block px-3 py-2 rounded-lg transition {{ !request('warehouse') ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                        Todos los almacenes
                    </a>
                </li>
                @foreach($warehouses as $warehouse)
                    <li>
                        @php
                            $warehouseParams = ['warehouse' => $warehouse->id];
                            if (request()->has('category')) {
                                $warehouseParams['category'] = request()->category;
                            }
                        @endphp
                        <a href="{{ request()->url() }}?{{ http_build_query($warehouseParams) }}" 
                           class="block px-3 py-2 rounded-lg transition {{ request('warehouse') == $warehouse->id ? 'bg-blue-50 text-blue-600 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                            {{ $warehouse->name }}
                            @if($warehouse->is_default)
                                <span class="text-xs text-gray-500">(Principal)</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Clear All Filters -->
        @if(count($activeFilters) > 0)
            <div class="pt-4 border-t border-gray-200">
                <a href="{{ $clearFiltersUrl }}" class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Limpiar Filtros
                </a>
            </div>
        @endif
    </div>
@endif

@if($showAsDropdowns)
    <!-- Dropdown Filters for category pages -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h4 class="font-semibold text-gray-800">Filtros adicionales:</h4>
            
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Warehouse Filter Only (category is determined by URL) -->
                <div class="relative">
                    <select id="warehouse-filter-dropdown" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                        <option value="">Todos los almacenes</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ isset($activeFilters['warehouse']) && $activeFilters['warehouse']['id'] == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}{{ $warehouse->is_default ? ' (Principal)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(count($activeFilters) > 0)
                    <a href="{{ $clearFiltersUrl }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Limpiar
                    </a>
                @endif
            </div>
        </div>

        <!-- Active Filters for Category Pages -->
        @if(count($filterBreadcrumbs) > 0)
            <div class="mt-3 pt-3 border-t border-gray-200">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-600">Filtros activos:</span>
                    @foreach($filterBreadcrumbs as $breadcrumb)
                        @if($breadcrumb['type'] === 'warehouse')
                            <span class="inline-flex items-center px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                {{ $breadcrumb['label'] }}
                                <a href="{{ $breadcrumb['remove_url'] }}" class="ml-1 text-blue-600 hover:text-blue-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle filter changes for header format
    const categoryFilter = document.getElementById('category-filter');
    const warehouseFilter = document.getElementById('warehouse-filter');
    
    function updateFilters() {
        const currentUrl = new URL(window.location);
        const params = new URLSearchParams(currentUrl.search);
        
        // Update category parameter
        if (categoryFilter && categoryFilter.value) {
            params.set('category', categoryFilter.value);
        } else if (categoryFilter) {
            params.delete('category');
        }
        
        // Update warehouse parameter
        if (warehouseFilter && warehouseFilter.value) {
            params.set('warehouse', warehouseFilter.value);
        } else if (warehouseFilter) {
            params.delete('warehouse');
        }
        
        // Redirect with new parameters
        currentUrl.search = params.toString();
        window.location.href = currentUrl.toString();
    }
    
    if (categoryFilter) {
        categoryFilter.addEventListener('change', updateFilters);
    }
    
    if (warehouseFilter) {
        warehouseFilter.addEventListener('change', updateFilters);
    }
    
    // Handle dropdown filters for category pages
    const warehouseDropdown = document.getElementById('warehouse-filter-dropdown');
    if (warehouseDropdown) {
        warehouseDropdown.addEventListener('change', function() {
            const currentUrl = new URL(window.location);
            const params = new URLSearchParams(currentUrl.search);
            
            if (this.value) {
                params.set('warehouse', this.value);
            } else {
                params.delete('warehouse');
            }
            
            currentUrl.search = params.toString();
            window.location.href = currentUrl.toString();
        });
    }
});
</script>
@endpush