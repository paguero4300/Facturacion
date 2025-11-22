<!--
    =============================================
    MODAL DE BIENVENIDA - SELECCIÓN DE TIENDA
    =============================================
    - Aparece solo si no hay warehouse seleccionado en URL ni cookie
    - Carga dinámica de almacenes activos
    - Geolocalización para sugerir tienda cercana
-->
<div id="welcomeModal" class="fixed inset-0 bg-black/60 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4 transition-all duration-500 opacity-0">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden transform scale-90 transition-all duration-500 ease-out" id="welcomeModalContent">
        
        <!-- Header with Pattern -->
        <div class="relative bg-gradient-to-br from-[var(--naranja)] to-orange-600 p-8 text-center overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'20\' height=\'20\' viewBox=\'0 0 20 20\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\' fill-rule=\'evenodd\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'3\'/%3E%3Ccircle cx=\'13\' cy=\'13\' r=\'3\'/%3E%3C/g%3E%3C/svg%3E');"></div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-3 tracking-tight">
                    ¡Bienvenido!
                </h2>
                <p class="text-orange-50 text-lg font-medium">
                    Elige tu tienda más cercana para ver ofertas exclusivas
                </p>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6 md:p-10 bg-gray-50">
            
            <!-- Loading State -->
            <div id="geoLoading" class="flex items-center justify-center gap-3 mb-8 text-gray-500 bg-white py-3 px-4 rounded-full shadow-sm mx-auto w-fit">
                <i class="fas fa-spinner fa-spin text-[var(--naranja)]"></i>
                <span class="text-sm font-medium">Detectando tu ubicación...</span>
            </div>

            <!-- Stores Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach(\App\Models\Warehouse::where('is_active', true)->orderBy('is_default', 'desc')->get() as $warehouse)
                    <button onclick="selectWarehouse({{ $warehouse->id }})" 
                            class="warehouse-option group relative bg-white p-6 rounded-2xl shadow-sm hover:shadow-xl border-2 border-transparent hover:border-[var(--naranja)] transition-all duration-300 text-left transform hover:-translate-y-1"
                            data-city="{{ strtolower($warehouse->name) }}">
                        
                        <!-- Recommended Badge -->
                        <div class="recommended-badge hidden absolute -top-3 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-bold px-4 py-1.5 rounded-full shadow-lg flex items-center gap-1">
                            <i class="fas fa-star text-yellow-300"></i> Recomendado
                        </div>

                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center group-hover:bg-[var(--naranja)] transition-colors duration-300">
                                <i class="fas fa-store text-xl text-[var(--naranja)] group-hover:text-white transition-colors duration-300"></i>
                            </div>
                            <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <i class="fas fa-chevron-right text-gray-400 group-hover:text-[var(--naranja)]"></i>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-[var(--naranja)] transition-colors">
                            {{ $warehouse->name }}
                        </h3>
                        
                        <p class="text-sm text-gray-500 flex items-start gap-2">
                            <i class="fas fa-map-marker-alt mt-1 text-gray-400"></i>
                            <span class="line-clamp-2">{{ $warehouse->address ?? 'Envíos a toda la ciudad' }}</span>
                        </p>
                    </button>
                @endforeach
            </div>

            <!-- Remember Choice -->
            <div class="flex items-center justify-center gap-3">
                <label class="flex items-center cursor-pointer relative">
                    <input type="checkbox" id="rememberChoice" checked class="peer sr-only">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[var(--naranja)]"></div>
                    <span class="ml-3 text-sm font-medium text-gray-600">Recordar mi elección</span>
                </label>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('welcomeModal');
        const content = document.getElementById('welcomeModalContent');
        const geoLoading = document.getElementById('geoLoading');
        
        // Verificar si debemos mostrar el modal
        const urlParams = new URLSearchParams(window.location.search);
        const hasWarehouseParam = urlParams.has('warehouse');
        
        if (!hasWarehouseParam) {
            // Mostrar modal
            modal.classList.remove('hidden');
            // Animación de entrada
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-90');
                content.classList.add('scale-100');
                detectLocation();
            }, 100);
        }

        // Función de geolocalización
        function detectLocation() {
            fetch('https://ipapi.co/json/')
                .then(response => response.json())
                .then(data => {
                    const city = data.city ? data.city.toLowerCase() : '';
                    highlightStore(city);
                })
                .catch(error => {
                    console.log('Error geolocalización:', error);
                    geoLoading.style.display = 'none';
                })
                .finally(() => {
                    setTimeout(() => {
                        if (geoLoading.style.display !== 'none') {
                            geoLoading.innerHTML = '<span class="text-gray-600">Selecciona tu tienda preferida</span>';
                        }
                    }, 1500);
                });
        }

        function highlightStore(userCity) {
            const options = document.querySelectorAll('.warehouse-option');
            let found = false;

            options.forEach(option => {
                const storeCity = option.dataset.city;
                if (userCity && storeCity.includes(userCity)) {
                    option.querySelector('.recommended-badge').classList.remove('hidden');
                    option.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
                    // Scroll suave hacia la opción si es necesario en móviles
                    option.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    found = true;
                }
            });

            if (found) {
                geoLoading.innerHTML = '<span class="text-blue-600 font-medium flex items-center gap-2"><i class="fas fa-map-marker-alt"></i> Te encuentras en ' + userCity.charAt(0).toUpperCase() + userCity.slice(1) + '</span>';
                geoLoading.classList.add('bg-blue-50', 'border', 'border-blue-100');
            } else {
                geoLoading.style.display = 'none';
            }
        }
    });

    function selectWarehouse(id) {
        const remember = document.getElementById('rememberChoice').checked;
        const modal = document.getElementById('welcomeModal');
        const content = document.getElementById('welcomeModalContent');

        // Animación de salida
        content.classList.add('scale-95', 'opacity-0');
        modal.classList.add('opacity-0');

        setTimeout(() => {
            if (remember) {
                const d = new Date();
                d.setTime(d.getTime() + (30*24*60*60*1000));
                let expires = "expires="+ d.toUTCString();
                document.cookie = "warehouse_id=" + id + ";" + expires + ";path=/";
            }

            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('warehouse', id);
            window.location.href = currentUrl.toString();
        }, 300);
    }
</script>
