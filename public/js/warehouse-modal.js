/**
 * =============================================
 * WAREHOUSE MODAL FUNCTIONALITY
 * =============================================
 * 
 * JavaScript para el modal de productos por almacén
 * - Manejo de estados del modal (apertura, cierre, carga)
 * - Comunicación con API endpoints
 * - Renderizado dinámico de productos
 * - Funcionalidad de búsqueda y paginación
 * - Manejo de errores y estados vacíos
 */

class WarehouseModal {
    constructor() {
        this.modal = null;
        this.currentWarehouse = null;
        this.currentPage = 1;
        this.searchTimeout = null;
        this.currentSearch = '';
        
        this.init();
    }

    init() {
        this.bindElements();
        this.bindEvents();
        console.log('WarehouseModal initialized');
    }

    bindElements() {
        // Modal elements
        this.modal = document.getElementById('warehouseModal');
        this.closeBtn = document.getElementById('closeModal');
        
        // Trigger buttons
        this.openBtn = document.getElementById('openWarehouseModal');
        this.openBtnEmpty = document.getElementById('openWarehouseModalEmpty');
        
        // Form elements
        this.warehouseSelect = document.getElementById('warehouseSelect');
        this.searchInput = document.getElementById('productSearch');
        this.searchContainer = document.getElementById('searchContainer');
        
        // Content containers
        this.loadingState = document.getElementById('loadingState');
        this.productsLoadingState = document.getElementById('productsLoadingState');
        this.warehouseInfo = document.getElementById('warehouseInfo');
        this.productsContainer = document.getElementById('productsContainer');
        this.productsGrid = document.getElementById('productsGrid');
        this.emptyState = document.getElementById('emptyState');
        this.errorState = document.getElementById('errorState');
        this.noSearchResults = document.getElementById('noSearchResults');
        
        // Info elements
        this.warehouseName = document.getElementById('warehouseName');
        this.warehouseCode = document.getElementById('warehouseCode');
        this.defaultBadge = document.getElementById('defaultBadge');
        this.productsCount = document.getElementById('productsCount');
        
        // Pagination
        this.paginationContainer = document.getElementById('paginationContainer');
        this.prevPageBtn = document.getElementById('prevPage');
        this.nextPageBtn = document.getElementById('nextPage');
        this.pageInfo = document.getElementById('pageInfo');
        
        // Template
        this.productTemplate = document.getElementById('productCardTemplate');
        
        // Retry button
        this.retryBtn = document.getElementById('retryButton');
    }

    bindEvents() {
        // Modal open/close events
        if (this.openBtn) {
            this.openBtn.addEventListener('click', () => this.openModal());
        }
        if (this.openBtnEmpty) {
            this.openBtnEmpty.addEventListener('click', () => this.openModal());
        }
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeModal());
        }
        
        // Close modal on outside click
        if (this.modal) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.closeModal();
                }
            });
        }
        
        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.closeModal();
            }
        });
        
        // Warehouse selection
        if (this.warehouseSelect) {
            this.warehouseSelect.addEventListener('change', (e) => {
                if (e.target.value) {
                    this.selectWarehouse(e.target.value);
                }
            });
        }
        
        // Search functionality
        if (this.searchInput) {
            this.searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }
        
        // Pagination
        if (this.prevPageBtn) {
            this.prevPageBtn.addEventListener('click', () => this.goToPage(this.currentPage - 1));
        }
        if (this.nextPageBtn) {
            this.nextPageBtn.addEventListener('click', () => this.goToPage(this.currentPage + 1));
        }
        
        // Retry button
        if (this.retryBtn) {
            this.retryBtn.addEventListener('click', () => this.loadWarehouses());
        }
    }

    openModal() {
        if (this.modal) {
            this.modal.classList.remove('hidden');
            this.modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            this.loadWarehouses();
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
            document.body.style.overflow = '';
            this.resetModal();
        }
    }

    resetModal() {
        this.currentWarehouse = null;
        this.currentPage = 1;
        this.currentSearch = '';
        
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        
        this.hideAllStates();
        this.searchContainer.classList.add('hidden');
    }

    hideAllStates() {
        const states = [
            this.loadingState,
            this.productsLoadingState,
            this.warehouseInfo,
            this.productsContainer,
            this.emptyState,
            this.errorState,
            this.noSearchResults,
            this.paginationContainer
        ];
        
        states.forEach(element => {
            if (element) {
                element.classList.add('hidden');
            }
        });
    }

    showState(state) {
        this.hideAllStates();
        if (state) {
            state.classList.remove('hidden');
        }
    }

    async loadWarehouses() {
        this.showState(this.loadingState);
        
        try {
            const response = await fetch('/api/warehouses');
            const data = await response.json();
            
            if (data.success && data.data) {
                this.populateWarehouseSelect(data.data);
                this.hideAllStates();
            } else {
                throw new Error(data.message || 'Error al cargar almacenes');
            }
        } catch (error) {
            console.error('Error loading warehouses:', error);
            this.showError('Error al cargar almacenes: ' + error.message);
        }
    }

    populateWarehouseSelect(warehouses) {
        if (!this.warehouseSelect) return;
        
        this.warehouseSelect.innerHTML = '<option value="">Selecciona un almacén...</option>';
        
        warehouses.forEach(warehouse => {
            const option = document.createElement('option');
            option.value = warehouse.id;
            option.textContent = `${warehouse.name} (${warehouse.code})`;
            if (warehouse.is_default) {
                option.textContent += ' ★';
            }
            this.warehouseSelect.appendChild(option);
        });
        
        this.warehouseSelect.disabled = false;
    }

    async selectWarehouse(warehouseId) {
        this.currentWarehouse = warehouseId;
        this.currentPage = 1;
        this.currentSearch = '';
        
        if (this.searchInput) {
            this.searchInput.value = '';
        }
        
        await this.loadProducts();
    }

    async loadProducts(page = 1, search = '') {
        if (!this.currentWarehouse) return;
        
        this.showState(this.productsLoadingState);
        this.currentPage = page;
        this.currentSearch = search;
        
        try {
            const params = new URLSearchParams({
                page: page.toString(),
                per_page: '20'
            });
            
            if (search.trim()) {
                params.append('search', search.trim());
            }
            
            const response = await fetch(`/api/warehouses/${this.currentWarehouse}/products?${params}`);
            const data = await response.json();
            
            if (data.success && data.data) {
                this.displayProducts(data.data);
            } else {
                throw new Error(data.message || 'Error al cargar productos');
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError('Error al cargar productos: ' + error.message);
        }
    }

    displayProducts(data) {
        // Update warehouse info
        this.updateWarehouseInfo(data.warehouse);
        
        // Show search container
        this.searchContainer.classList.remove('hidden');
        
        const products = data.products || [];
        const pagination = data.pagination || {};
        
        if (products.length === 0) {
            if (this.currentSearch) {
                this.showState(this.noSearchResults);
            } else {
                this.showState(this.emptyState);
            }
            return;
        }
        
        // Update products count
        const totalText = pagination.total_products 
            ? `${pagination.total_products} producto${pagination.total_products !== 1 ? 's' : ''}`
            : `${products.length} producto${products.length !== 1 ? 's' : ''}`;
        this.productsCount.textContent = totalText;
        
        // Render products
        this.renderProducts(products);
        
        // Update pagination
        this.updatePagination(pagination);
        
        // Show products container
        this.showState(this.productsContainer);
    }

    updateWarehouseInfo(warehouse) {
        if (this.warehouseName) {
            this.warehouseName.textContent = warehouse.name || '-';
        }
        if (this.warehouseCode) {
            this.warehouseCode.textContent = warehouse.code || '-';
        }
        if (this.defaultBadge) {
            if (warehouse.is_default) {
                this.defaultBadge.classList.remove('hidden');
            } else {
                this.defaultBadge.classList.add('hidden');
            }
        }
        if (this.warehouseInfo) {
            this.warehouseInfo.classList.remove('hidden');
        }
    }

    renderProducts(products) {
        if (!this.productsGrid || !this.productTemplate) return;
        
        this.productsGrid.innerHTML = '';
        
        products.forEach(product => {
            const productCard = this.createProductCard(product);
            this.productsGrid.appendChild(productCard);
        });
    }

    createProductCard(product) {
        const template = this.productTemplate.content;
        const card = document.importNode(template, true);
        
        // Set product image
        const img = card.querySelector('.product-image');
        if (img) {
            img.src = product.image_url || 'https://via.placeholder.com/300x300?text=Sin+Imagen';
            img.alt = product.name || 'Producto';
        }
        
        // Set product info
        const name = card.querySelector('.product-name');
        if (name) name.textContent = product.name || 'Sin nombre';
        
        const code = card.querySelector('.product-code');
        if (code) code.textContent = `Código: ${product.code || 'N/A'}`;
        
        const price = card.querySelector('.product-price');
        if (price) price.textContent = `S/ ${product.price || '0.00'}`;
        
        // Set stock info
        const stockQty = card.querySelector('.stock-qty');
        const stockBadge = card.querySelector('.stock-badge');
        const stockBar = card.querySelector('.stock-bar');
        
        if (product.stock) {
            if (stockQty) stockQty.textContent = product.stock.qty;
            
            // Stock status badge and progress bar
            const status = this.getStockStatus(product.stock);
            if (stockBadge) {
                stockBadge.textContent = status.text;
                stockBadge.className = `stock-badge absolute top-2 right-2 text-white text-xs font-bold px-2 py-1 rounded-full z-10 ${status.class}`;
            }
            
            if (stockBar) {
                const percentage = this.getStockPercentage(product.stock);
                stockBar.style.width = `${percentage}%`;
                stockBar.className = `stock-bar h-full transition-all duration-300 ${status.barClass}`;
            }
        }
        
        // Set form data
        const form = card.querySelector('.add-to-cart-form');
        if (form) {
            const productIdInput = form.querySelector('input[name="product_id"]');
            if (productIdInput) productIdInput.value = product.id;
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && product.stock && product.stock.qty_raw <= 0) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-ban mr-2"></i>Sin Stock';
            }
        }
        
        return card;
    }

    getStockStatus(stock) {
        if (stock.qty_raw <= 0) {
            return {
                text: 'Sin Stock',
                class: 'bg-red-500',
                barClass: 'bg-red-500'
            };
        } else if (stock.status === 'low_stock') {
            return {
                text: 'Stock Bajo',
                class: 'bg-yellow-500',
                barClass: 'bg-yellow-500'
            };
        } else {
            return {
                text: 'Disponible',
                class: 'bg-green-500',
                barClass: 'bg-green-500'
            };
        }
    }

    getStockPercentage(stock) {
        if (stock.qty_raw <= 0) return 0;
        if (stock.min_qty_raw <= 0) return 100;
        
        const ratio = stock.qty_raw / (stock.min_qty_raw * 2); // Usar el doble del mínimo como 100%
        return Math.min(100, Math.max(5, ratio * 100));
    }

    updatePagination(pagination) {
        if (!this.paginationContainer) return;
        
        const hasPages = pagination.total_pages > 1;
        
        if (!hasPages) {
            this.paginationContainer.classList.add('hidden');
            return;
        }
        
        this.paginationContainer.classList.remove('hidden');
        
        // Update page info
        if (this.pageInfo) {
            this.pageInfo.textContent = `Página ${pagination.current_page} de ${pagination.total_pages}`;
        }
        
        // Update buttons
        if (this.prevPageBtn) {
            this.prevPageBtn.disabled = !pagination.has_prev;
        }
        if (this.nextPageBtn) {
            this.nextPageBtn.disabled = !pagination.has_next;
        }
    }

    handleSearch(searchTerm) {
        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }
        
        // Set new timeout for debounced search
        this.searchTimeout = setTimeout(() => {
            if (this.currentWarehouse) {
                this.loadProducts(1, searchTerm);
            }
        }, 300);
    }

    goToPage(page) {
        if (page < 1 || !this.currentWarehouse) return;
        this.loadProducts(page, this.currentSearch);
    }

    showError(message) {
        this.showState(this.errorState);
        const errorMessage = this.errorState.querySelector('p');
        if (errorMessage) {
            errorMessage.textContent = message;
        }
    }
}

// Initialize the modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.warehouseModal = new WarehouseModal();
});