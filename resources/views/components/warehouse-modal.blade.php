<!--
    =============================================
    MODAL DE PRODUCTOS POR ALMACÉN
    =============================================
    - Modal responsivo para mostrar productos filtrados por almacén
    - Incluye selector de almacén y grid de productos con stock
    - Estados de carga y manejo de errores
    - Diseño consistente con el estilo de la aplicación
-->

<!-- Modal -->
<div id="warehouseModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b" style="background-color: var(--fondo-productos);">
            <div>
                <h2 class="text-2xl font-bold" style="color: var(--enlaces-titulos);">
                    <i class="fas fa-warehouse mr-2" style="color: var(--naranja);"></i>
                    Productos por Almacén
                </h2>
                <p class="text-sm mt-1" style="color: var(--texto-principal);">
                    Explora nuestro inventario disponible por ubicación
                </p>
            </div>
            <button id="closeModal" class="text-gray-400 hover:text-gray-600 transition p-2">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-auto max-h-[calc(90vh-140px)]">
            <!-- Warehouse Selector -->
            <div class="mb-6">
                <label for="warehouseSelect" class="block text-sm font-semibold mb-2" style="color: var(--enlaces-titulos);">
                    Seleccionar Almacén:
                </label>
                <select id="warehouseSelect" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <option value="">Cargando almacenes...</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="mb-6 hidden" id="searchContainer">
                <div class="relative">
                    <input type="text" id="productSearch" placeholder="Buscar productos por nombre o código..." 
                           class="w-full p-3 pl-10 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="text-center py-12 hidden">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto mb-4" style="border-color: var(--naranja);"></div>
                <p class="text-gray-600">Cargando almacenes...</p>
            </div>

            <!-- Products Loading State -->
            <div id="productsLoadingState" class="text-center py-12 hidden">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 mx-auto mb-4" style="border-color: var(--naranja);"></div>
                <p class="text-gray-600">Cargando productos...</p>
            </div>

            <!-- Warehouse Info -->
            <div id="warehouseInfo" class="hidden mb-6 p-4 rounded-lg" style="background-color: var(--fondo-productos);">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold" style="color: var(--enlaces-titulos);">
                            <i class="fas fa-warehouse mr-2" style="color: var(--naranja);"></i>
                            <span id="warehouseName">-</span>
                        </h3>
                        <p class="text-sm" style="color: var(--texto-principal);">
                            Código: <span id="warehouseCode">-</span>
                        </p>
                    </div>
                    <div id="defaultBadge" class="hidden px-3 py-1 rounded-full text-xs font-semibold text-white" style="background-color: var(--naranja);">
                        <i class="fas fa-star mr-1"></i>Por Defecto
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div id="productsContainer" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold" style="color: var(--enlaces-titulos);">
                        Productos Disponibles
                    </h4>
                    <span id="productsCount" class="text-sm px-3 py-1 rounded-full" style="background-color: var(--fondo-productos); color: var(--texto-principal);">
                        0 productos
                    </span>
                </div>
                
                <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Los productos se cargarán aquí dinámicamente -->
                </div>

                <!-- Pagination -->
                <div id="paginationContainer" class="mt-6 flex items-center justify-center gap-2 hidden">
                    <button id="prevPage" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="pageInfo" class="px-4 py-2 text-sm" style="color: var(--texto-principal);">
                        Página 1 de 1
                    </span>
                    <button id="nextPage" class="px-4 py-2 border rounded-lg hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="text-center py-12 hidden">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2" style="color: var(--enlaces-titulos);">No hay productos disponibles</h3>
                <p class="text-gray-500">No se encontraron productos en este almacén.</p>
            </div>

            <!-- Error State -->
            <div id="errorState" class="text-center py-12 hidden">
                <i class="fas fa-exclamation-triangle text-6xl text-red-300 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2 text-red-600">Error al cargar datos</h3>
                <p class="text-gray-500 mb-4">Ocurrió un error al obtener la información.</p>
                <button id="retryButton" class="px-6 py-2 rounded-lg text-white font-semibold hover:opacity-90 transition" style="background-color: var(--naranja);">
                    <i class="fas fa-retry mr-2"></i>Reintentar
                </button>
            </div>

            <!-- No Search Results -->
            <div id="noSearchResults" class="text-center py-12 hidden">
                <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2" style="color: var(--enlaces-titulos);">Sin resultados</h3>
                <p class="text-gray-500">No se encontraron productos que coincidan con tu búsqueda.</p>
            </div>
        </div>
    </div>
</div>

<!-- Product Card Template (Hidden) -->
<template id="productCardTemplate">
    <div class="product-card rounded-xl overflow-hidden shadow-md hover:shadow-lg transition" style="background-color: var(--fondo-productos);">
        <div class="relative aspect-square overflow-hidden">
            <span class="stock-badge absolute top-2 right-2 text-white text-xs font-bold px-2 py-1 rounded-full z-10">
                <!-- Stock status will be set here -->
            </span>
            <img class="product-image w-full h-full object-cover hover:scale-105 transition duration-300" 
                 src="" alt="" loading="lazy"
                 onerror="this.src='https://via.placeholder.com/300x300?text=Sin+Imagen'">
        </div>
        <div class="p-4">
            <h3 class="product-name font-bold mb-1 truncate" style="color: var(--enlaces-titulos);"></h3>
            <p class="product-code text-xs mb-2 opacity-75" style="color: var(--texto-principal);"></p>
            <p class="product-price font-bold mb-2" style="color: var(--precio-actual);"></p>
            <div class="stock-info mb-3">
                <div class="flex items-center justify-between text-sm mb-1">
                    <span style="color: var(--texto-principal);">Stock:</span>
                    <span class="stock-qty font-semibold"></span>
                </div>
                <div class="stock-progress bg-gray-200 rounded-full h-2 overflow-hidden">
                    <div class="stock-bar h-full transition-all duration-300"></div>
                </div>
            </div>
            <form class="add-to-cart-form" action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="w-full text-white py-2 rounded-lg transition font-semibold hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed" 
                        style="background-color: var(--naranja);">
                    <i class="fas fa-cart-plus mr-2"></i>Añadir al Carrito
                </button>
            </form>
        </div>
    </div>
</template>