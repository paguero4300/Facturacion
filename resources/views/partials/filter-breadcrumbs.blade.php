@if(isset($filterBreadcrumbs) && count($filterBreadcrumbs) > 0)
    <div class=\"bg-gradient-to-r from-blue-50 to-pink-50 border-l-4 border-pink-500 p-4 mb-6 rounded-r-lg\">
        <div class=\"flex flex-wrap items-center gap-2\">
            <div class=\"flex items-center text-sm text-gray-600\">
                <i class=\"fas fa-filter mr-2 text-pink-500\"></i>
                <span class=\"font-medium\">Filtros activos:</span>
            </div>
            
            @foreach($filterBreadcrumbs as $breadcrumb)
                <div class=\"inline-flex items-center\">
                    @if($breadcrumb['type'] === 'category')
                        <span class=\"inline-flex items-center px-3 py-1 text-sm bg-pink-100 text-pink-800 rounded-full font-medium\">
                            <i class=\"fas fa-tag mr-1\"></i>
                            {{ $breadcrumb['label'] }}
                            <a href=\"{{ $breadcrumb['remove_url'] }}\" class=\"ml-2 text-pink-600 hover:text-pink-800 transition\">
                                <i class=\"fas fa-times\"></i>
                            </a>
                        </span>
                    @elseif($breadcrumb['type'] === 'warehouse')
                        <span class=\"inline-flex items-center px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full font-medium\">
                            <i class=\"fas fa-warehouse mr-1\"></i>
                            {{ $breadcrumb['label'] }}
                            <a href=\"{{ $breadcrumb['remove_url'] }}\" class=\"ml-2 text-blue-600 hover:text-blue-800 transition\">
                                <i class=\"fas fa-times\"></i>
                            </a>
                        </span>
                    @endif
                </div>
            @endforeach
            
            @if(isset($clearFiltersUrl))
                <div class=\"ml-auto\">
                    <a href=\"{{ $clearFiltersUrl }}\" class=\"inline-flex items-center px-3 py-1 text-sm text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition\">
                        <i class=\"fas fa-times-circle mr-1\"></i>
                        Limpiar todos
                    </a>
                </div>
            @endif
        </div>
        
        @if(isset($filteredProductsCount))
            <div class=\"mt-2 text-sm text-gray-600\">
                <i class=\"fas fa-info-circle mr-1 text-blue-500\"></i>
                {{ $filteredProductsCount }} {{ $filteredProductsCount === 1 ? 'producto encontrado' : 'productos encontrados' }} con los filtros aplicados
            </div>
        @endif
    </div>
@endif