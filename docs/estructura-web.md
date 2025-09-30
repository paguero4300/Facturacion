# Estructura Web - Página /detalles

## Información General

**URL:** `http://facturacion.test/detalles`
**Ruta Laravel:** `GET /detalles`
**Controlador:** `App\Http\Controllers\DetallesController@index`
**Vista Principal:** `resources/views/index.blade.php`
**Layout:** `resources/views/layouts/app.blade.php`

---

## 1. Arquitectura de Rutas

### Archivo: `routes/web.php`

```php
// Rutas para la página de Detalles (líneas 12-15)
Route::prefix('detalles')->name('detalles.')->group(function () {
    Route::get('/', [DetallesController::class, 'index'])->name('index');
    Route::post('/contacto', [DetallesController::class, 'submitContact'])->name('contacto.submit');
});

// Ruta dinámica para categorías (línea 19)
Route::get('/{categorySlug}', [DetallesController::class, 'showCategory'])->name('category.show');
```

### Rutas Disponibles:
- **GET /detalles** - Página principal (index)
- **POST /detalles/contacto** - Envío de formulario de contacto
- **GET /{categorySlug}** - Muestra productos de una categoría específica

---

## 2. Controlador

### Archivo: `app/Http/Controllers/DetallesController.php`

#### Método `index()` (líneas 16-26)
```php
public function index()
{
    $menuCategories = Category::where('status', true)
        ->parents()
        ->with('activeChildren')
        ->get();

    $mainCategories = $menuCategories;

    return view('index', compact('menuCategories', 'mainCategories'));
}
```
- **Propósito:** Renderiza la vista principal cargando categorías para menú y sección principal
- **Consultas:**
  - Carga categorías activas principales (sin padre)
  - Eager loading de subcategorías activas
  - Misma colección se usa para menú y sección de categorías
- **Vista:** `resources/views/index.blade.php`
- **Datos pasados:** `$menuCategories`, `$mainCategories`

#### Método `showCategory()` (líneas 28-67)
```php
public function showCategory(string $categorySlug)
{
    $category = Category::where('slug', $categorySlug)
        ->where('status', true)
        ->with([
            'products' => function ($query) {
                $query->where('status', 'active')
                      ->where('for_sale', true)
                      ->orderBy('name', 'asc');
            },
            'parent.activeChildren',
            'activeChildren'
        ])
        ->firstOrFail();

    // Si es una categoría padre (tiene subcategorías), cargar todos los productos
    // incluyendo los de las subcategorías
    if ($category->hasChildren()) {
        $categoryIds = $category->activeChildren->pluck('id')->push($category->id);

        $products = Product::whereIn('category_id', $categoryIds)
            ->where('status', 'active')
            ->where('for_sale', true)
            ->orderBy('name', 'asc')
            ->get();
    } else {
        // Si es una subcategoría, solo mostrar sus productos
        $products = $category->products;
    }

    $menuCategories = Category::where('status', true)
        ->parents()
        ->with('activeChildren')
        ->get();

    return view('category', [
        'category' => $category,
        'products' => $products,
        'menuCategories' => $menuCategories,
    ]);
}
```
- **Propósito:** Muestra una categoría específica con lógica inteligente de productos
- **Parámetros:** `$categorySlug` - Slug de la categoría
- **Lógica de Productos:**
  - **Si es categoría padre (tiene subcategorías):** Muestra productos de la categoría Y todas sus subcategorías activas
  - **Si es subcategoría:** Solo muestra productos de esa categoría específica
- **Eager Loading:**
  - Productos activos y disponibles para venta, ordenados alfabéticamente
  - Categoría padre con sus subcategorías (para breadcrumbs)
  - Subcategorías activas de la categoría actual
- **Vista:** `resources/views/category.blade.php`
- **Datos pasados:** `$category`, `$products`, `$menuCategories`

#### Método `submitContact()` (líneas 69-83)
```php
public function submitContact(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'message' => 'required|string|max:1000',
    ]);

    return redirect()->route('contacto')
        ->with('success', '¡Mensaje enviado con éxito! Nos pondremos en contacto contigo pronto.');
}
```
- **Propósito:** Procesa formulario de contacto
- **Validación:** name, phone, email, message (todos requeridos)
- **Nota:** Actualmente solo valida y redirige (sin lógica de envío de email o guardado en BD)

---

## 3. Vista Principal

### Archivo: `resources/views/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'Detalles - Tienda de Regalos')

@section('content')
    @include('partials.hero')
    @include('partials.benefits')
    @include('partials.categories')
    @include('partials.featured-products')
    @include('partials.contact-form')
@endsection
```

**Estructura:**
1. Extiende layout principal (`layouts.app`)
2. Define título de página
3. Incluye 5 partials principales en orden secuencial

---

## 4. Layout Principal

### Archivo: `resources/views/layouts/app.blade.php`

#### Estructura HTML (líneas 1-69):

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Detalles - Tienda de Regalos')</title>

    <!-- CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @stack('styles')
</head>

<body style="background-color: var(--fondo-principal);">
    @include('partials.header')

    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @stack('scripts')

    <!-- Script de lazy loading de imágenes (líneas 30-66) -->
</body>
</html>
```

#### Recursos Externos:
- **TailwindCSS:** CDN para estilos utility-first
- **Swiper JS:** v11 para carruseles/sliders
- **Styles.css:** Archivo de estilos personalizados

#### Scripts Incluidos:
1. **Lazy Loading de Imágenes** (líneas 30-50):
   - Usa `IntersectionObserver` para cargar imágenes cuando entran en viewport
   - Aplica clase `.loaded` cuando la imagen se carga
   - Observa elementos con clase `.lazy-load`

2. **Manejo de Errores de Imágenes** (líneas 52-63):
   - Detecta errores de carga con evento `error`
   - Reemplaza con placeholder de `via.placeholder.com`
   - Usa texto del atributo `alt` en el placeholder

---

## 5. Componentes/Partials

### 5.1 Header (Navigation)
**Archivo:** `resources/views/partials/header.blade.php`

#### Estructura (líneas 10-60):
```html
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
        <!-- Logo -->
        <img src="{{ asset('logos/logo_horizontal.png') }}" alt="Detalles y Más" class="h-12">

        <!-- Menú de navegación -->
        <ul class="hidden md:flex gap-6 text-sm font-medium">
            <li><a href="{{ route('inicio') }}">INICIO</a></li>
            <li><a href="{{ route('nosotros') }}">NOSOTROS</a></li>

            <!-- Categorías dinámicas con subcategorías -->
            @foreach($menuCategories as $category)
                <li class="relative group">
                    <a href="{{ url('/' . $category->slug) }}">
                        {{ strtoupper($category->name) }}
                    </a>

                    <!-- Dropdown de subcategorías -->
                    @if($category->activeChildren->count() > 0)
                        <div class="dropdown">
                            @foreach($category->activeChildren as $subcategory)
                                <a href="{{ url('/' . $subcategory->slug) }}">
                                    {{ $subcategory->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>

        <!-- Iconos de acción -->
        <div class="flex gap-4 text-sm">
            <a href="{{ route('buscar') }}">🔍</a>
            <a href="{{ route('usuario') }}">👤</a>
            <a href="{{ route('carrito') }}" class="relative">
                🛒
                <span class="badge">0</span>
            </a>
        </div>
    </nav>
</header>
```

#### Características:
- **Sticky header:** Fijo en la parte superior al hacer scroll
- **Logo:** `logos/logo_horizontal.png` (altura 48px)
- **Navegación dinámica:**
  - Enlaces estáticos: Inicio, Nosotros
  - Categorías cargadas desde BD vía ViewComposer
  - Dropdown para subcategorías activas
- **Iconos de acción:**
  - Búsqueda (🔍)
  - Usuario (👤)
  - Carrito (🛒) con contador de items (actualmente estático: 0)
- **Responsive:** Menú oculto en móvil (`.hidden md:flex`)

#### Datos Inyectados:
- **$menuCategories:** Inyectado por ViewComposer en `AppServiceProvider` (líneas 51-62)

---

### 5.2 Hero Section
**Archivo:** `resources/views/partials/hero.blade.php`

#### Estructura (líneas 11-195):

**Sección Principal:**
- Diseño de 2 columnas (grid md:grid-cols-2)
- Gradiente de fondo personalizado
- Elementos decorativos flotantes con animaciones

**Columna Izquierda - Contenido Textual:**

1. **Badge animado** (líneas 38-43):
   ```html
   <div class="badge animate-pulse">
       <span class="dot"></span>
       Detalles y Más
   </div>
   ```

2. **Título principal** (líneas 46-52):
   ```html
   <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold">
       <span class="gradient-text">Detalles</span>
       <br>
       <span>que enamoran</span>
   </h1>
   ```
   - Título con gradiente (naranja → azul claro)
   - Subtítulo en color de enlaces

3. **Subtítulo descriptivo** (líneas 55-58):
   ```html
   <p class="text-lg md:text-xl">
       Creamos momentos especiales con flores frescas
       y regalos únicos para cada ocasión importante de tu vida.
   </p>
   ```

4. **Tarjeta de contacto** (líneas 62-86):
   ```html
   <div class="contact-card">
       <img src="{{ asset('logos/herocontac1.jpg') }}" class="w-40 h-40">
       <div class="contact-info">
           <p class="phone">🌻 (51) 944 492 316</p>
           <p class="cta">Llámanos para hacer tu pedido</p>
           <span class="badge">Disponible 24/7</span>
       </div>
   </div>
   ```
   - Imagen circular con borde naranja
   - Teléfono clickeable
   - Badge de disponibilidad

5. **Mensaje emocional** (líneas 89-96):
   ```html
   <div class="testimonial">
       "Tu confianza nos inspira a crear momentos inolvidables.
       En Detalles, cada flor cuenta una historia..."
   </div>
   ```
   - Diseño en cursiva con gradiente de fondo
   - Borde izquierdo naranja

6. **Botones de acción** (líneas 99-121):
   ```html
   <a href="{{ route('productos') }}" class="btn-primary">
       Ver Productos →
   </a>
   <a href="{{ route('contacto') }}" class="btn-secondary">
       Contáctanos
   </a>
   ```
   - Botón primario: Fondo naranja con hover azul
   - Botón secundario: Borde naranja con hover

7. **Redes sociales** (líneas 124-150):
   ```html
   <div class="social-icons">
       <a href="#" title="Instagram">🌸</a>
       <a href="#" title="Facebook">🌺</a>
       <a href="#" title="WhatsApp">💐</a>
       <a href="#" title="Pinterest">🌻</a>
       <a href="#" title="Ubicación">📍</a>
   </div>
   ```
   - Iconos emojis temáticos (flores)
   - Efecto hover con scale

**Columna Derecha - Imagen Hero:**

8. **Imagen principal** (líneas 154-183):
   ```html
   <div class="hero-image-container">
       <img src="{{ asset('logos/herosection.png') }}"
            alt="Hermoso arreglo de flores y regalos"
            class="lazy-load"
            loading="eager">
       <div class="overlay"></div>
   </div>
   ```
   - Proporción 4:3 con `aspect-[4/3]`
   - Lazy loading habilitado
   - Overlay con gradiente sutil
   - Elementos decorativos (círculos naranja/azul)
   - Efecto hover con scale

**Decoración Final:**

9. **Ola decorativa** (líneas 188-194):
   ```html
   <svg viewBox="0 0 1440 120" fill="none">
       <path d="..." fill="white" />
   </svg>
   ```
   - SVG wave al final de la sección
   - Transición suave a sección siguiente

#### Características Técnicas:
- **Responsive:** Stack vertical en móvil, horizontal en desktop
- **Animaciones:**
  - `animate-pulse` en badge y elementos decorativos
  - `animate-bounce` en círculos flotantes
  - Delays en animaciones para efecto escalonado
- **Accesibilidad:**
  - Alt texts descriptivos
  - Loading eager para imagen principal
  - Fallback de error para imágenes
- **Performance:**
  - Lazy loading con clase `.lazy-load`
  - Loading eager en imagen principal (above the fold)

---

### 5.3 Benefits Section
**Archivo:** `resources/views/partials/benefits.blade.php`

#### Estructura (líneas 10-33):
```html
<section class="container mx-auto px-4 pb-12">
    <div class="max-w-5xl mx-auto grid grid-cols-2 md:grid-cols-4 gap-6">
        <!-- Beneficio 1: Envío Rápido -->
        <div class="benefit-card">
            <div class="icon">🚚</div>
            <p class="title">Envío Rápido</p>
            <p class="description">Mismo día disponible</p>
        </div>

        <!-- Beneficio 2: Pago Seguro -->
        <div class="benefit-card">
            <div class="icon">💳</div>
            <p class="title">Pago Seguro</p>
            <p class="description">Múltiples métodos</p>
        </div>

        <!-- Beneficio 3: Calidad Premium -->
        <div class="benefit-card">
            <div class="icon">🌟</div>
            <p class="title">Calidad Premium</p>
            <p class="description">Productos seleccionados</p>
        </div>

        <!-- Beneficio 4: Soporte 24/7 -->
        <div class="benefit-card">
            <div class="icon">💬</div>
            <p class="title">Soporte 24/7</p>
            <p class="description">Siempre disponibles</p>
        </div>
    </div>
</section>
```

#### Características:
- **Layout:** Grid responsivo (2 columnas en móvil, 4 en desktop)
- **Máximo ancho:** 5xl (contenido centrado)
- **4 Beneficios principales:**
  1. **Envío Rápido** - Mismo día disponible
  2. **Pago Seguro** - Múltiples métodos
  3. **Calidad Premium** - Productos seleccionados
  4. **Soporte 24/7** - Siempre disponibles
- **Estilos:** Tarjetas con fondo blanco, sombra suave y hover effect
- **Iconos:** Emojis grandes (text-3xl)

---

### 5.4 Categories Section
**Archivo:** `resources/views/partials/categories.blade.php`

#### Estructura (líneas 10-88):

**Encabezado de Sección** (líneas 11-14):
```html
<section id="productos" class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <p class="text-sm font-semibold uppercase" style="color: var(--naranja);">
            Nuestras Categorías
        </p>
        <h2 class="text-3xl md:text-4xl font-bold">
            Explora Nuestros Productos
        </h2>
    </div>
```

**Lógica Condicional** (líneas 16-87):

1. **Si hay categorías disponibles** (`$mainCategories->isNotEmpty()`):

   a. **Grid estático para ≤4 categorías** (líneas 17-44):
   ```blade
   @if($mainCategories->count() <= 4)
       <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
           @foreach($mainCategories as $category)
               <a href="{{ url('/' . $category->slug) }}" class="category-card">
                   <div class="aspect-[4/3] overflow-hidden">
                       @if($category->image)
                           <img src="{{ asset('storage/' . $category->image) }}"
                                alt="{{ $category->name }}"
                                class="w-full h-full object-cover group-hover:scale-110"
                                loading="lazy">
                       @else
                           <img src="{{ asset('images/no-image.png') }}" ...>
                       @endif
                   </div>
                   <div class="p-4">
                       <h3 class="font-bold text-lg">{{ $category->name }}</h3>
                       <p class="text-sm">{{ $category->description ?? 'Ver productos' }}</p>
                   </div>
               </a>
           @endforeach
       </div>
   @endif
   ```

   b. **Swiper slider para >4 categorías** (líneas 45-81):
   ```blade
   @else
       <div class="swiper categoriesSwiper">
           <div class="swiper-wrapper">
               @foreach($mainCategories as $category)
                   <div class="swiper-slide">
                       <!-- Misma estructura de tarjeta -->
                   </div>
               @endforeach
           </div>
           <div class="swiper-pagination"></div>
           <div class="swiper-button-next"></div>
           <div class="swiper-button-prev"></div>
       </div>
   @endif
   ```

2. **Si no hay categorías** (líneas 83-86):
   ```html
   <div class="text-center py-8">
       <p class="text-gray-500">No hay categorías disponibles</p>
   </div>
   ```

#### Script de Inicialización de Swiper (líneas 90-129):
```javascript
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const totalSlides = {{ ($mainCategories ?? collect())->count() }};

    const swiper = new Swiper('.categoriesSwiper', {
        slidesPerView: 2,
        spaceBetween: 20,
        loop: totalSlides > 6,
        centeredSlides: totalSlides <= 4,
        autoplay: totalSlides > 4 ? {
            delay: 3000,
            disableOnInteraction: false,
        } : false,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: totalSlides >= 3 ? 3 : 2,
                spaceBetween: 24,
            },
            1024: {
                slidesPerView: totalSlides >= 4 ? 3 : totalSlides,
                spaceBetween: 24,
            },
        },
    });
});
</script>
@endpush
```

#### Características:
- **Decisión dinámica:** Grid vs Swiper basado en cantidad de categorías
- **Responsive breakpoints:**
  - Móvil (<640px): 2 slides
  - Tablet (640-1024px): 2-3 slides
  - Desktop (>1024px): 3-4 slides
- **Autoplay:** Solo si hay más de 4 categorías
- **Loop:** Solo si hay más de 6 categorías
- **Imágenes:**
  - Carga desde `storage/` si existe
  - Fallback a `images/no-image.png`
  - Lazy loading habilitado
  - Efecto hover con scale-110
- **Datos inyectados:**
  - `$mainCategories`: ViewComposer en `AppServiceProvider` (líneas 65-73)

---

### 5.5 Featured Products Section
**Archivo:** `resources/views/partials/featured-products.blade.php`

#### Estructura (líneas 10-90):

**Encabezado** (líneas 11-14):
```html
<section class="container mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <p class="text-sm font-semibold uppercase" style="color: var(--naranja);">
            Nuestros Productos
        </p>
        <h2 class="text-3xl md:text-4xl font-bold">
            Productos Destacados
        </h2>
    </div>
```

**Grid de Productos** (líneas 15-84):
```html
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-6xl mx-auto">
    <!-- Producto 1: Peluche 20cm -->
    <div class="product-card">
        <div class="relative aspect-square">
            <span class="badge-oferta">OFERTA</span>
            <img src="[unsplash URL]" alt="Peluche 20cm" class="lazy-load" loading="lazy">
        </div>
        <div class="p-4">
            <h3 class="font-bold">Peluche 20 CM</h3>
            <p class="font-bold price">S/ 30.00 - S/ 35.00</p>
            <p class="text-sm description">Peluches adorables</p>
            <button class="btn-add-cart">Añadir al Carrito</button>
        </div>
    </div>

    <!-- Producto 2: Peluche 30cm -->
    <div class="product-card">...</div>

    <!-- Producto 3: Peluche 40cm -->
    <div class="product-card">...</div>

    <!-- Producto 4: Rosas Rojas -->
    <div class="product-card">...</div>
</div>
```

**Botón "Ver Todos"** (líneas 85-89):
```html
<div class="text-center mt-10">
    <button class="btn-outline">Ver Todos los Productos</button>
</div>
```

#### Productos Mostrados:

| # | Producto | Precio | Imagen | Descripción |
|---|----------|--------|--------|-------------|
| 1 | Peluche 20 CM | S/ 30.00 - S/ 35.00 | [Unsplash] | Peluches adorables |
| 2 | Peluche 30 CM | S/ 40.00 - S/ 45.00 | [Unsplash] | Peluches medianos |
| 3 | Peluche 40 CM | S/ 60.00 - S/ 65.00 | [Unsplash] | Peluches grandes |
| 4 | 6 Rosas Rojas | S/ 75.00 - S/ 80.00 | [Unsplash] | Flores frescas |

#### Características:
- **Layout:** Grid 2x2 en móvil, 4x1 en desktop
- **Imágenes:**
  - Proporción cuadrada (aspect-square)
  - Fuente: Unsplash (URLs externas)
  - Lazy loading con fallback a placeholder
- **Etiqueta "OFERTA":**
  - Posición absoluta (top-3 left-3)
  - Fondo rojo intenso
  - Fuente blanca y bold
- **Botón "Añadir al Carrito":**
  - Ancho completo
  - Fondo naranja
  - Hover effect
- **Precios:**
  - Rango de precios mostrado
  - Color negro (var(--precio-actual))
- **Nota:** Productos estáticos (hardcoded), no cargados desde BD

---

### 5.6 Contact Form Section
**Archivo:** `resources/views/partials/contact-form.blade.php`

#### Estructura (líneas 10-63):

**Contenedor Principal** (líneas 11-16):
```html
<section id="contacto" class="container mx-auto px-4 py-16">
    <div class="max-w-2xl mx-auto rounded-2xl shadow-xl p-8 md:p-12 relative overflow-hidden">
        <!-- Imagen de fondo -->
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('logos/contact_form.jpg') }}" class="w-full h-full object-cover opacity-20">
        </div>
```

**Encabezado** (líneas 18-23):
```html
<div class="text-center mb-8 relative z-10">
    <p class="text-sm font-semibold uppercase" style="color: var(--naranja);">
        Contacta Con Nosotros
    </p>
    <h2 class="text-3xl md:text-4xl font-bold mb-4">
        ¿Tienes dudas? Estamos aquí para ayudarte
    </h2>
    <p>Completa el formulario y te responderemos lo más pronto posible</p>
</div>
```

**Formulario** (líneas 24-61):
```html
<form class="space-y-5 relative z-10"
      method="POST"
      action="{{ route('detalles.contacto.submit') }}">
    @csrf

    <!-- Campo: Nombre -->
    <div>
        <label class="block font-semibold mb-2">Nombre *</label>
        <input type="text"
               name="name"
               placeholder="Tu nombre completo"
               required
               class="w-full px-4 py-3 border rounded-lg">
        @error('name')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Campo: Teléfono -->
    <div>
        <label class="block font-semibold mb-2">Teléfono *</label>
        <input type="tel"
               name="phone"
               placeholder="Tu número de teléfono"
               required
               class="w-full px-4 py-3 border rounded-lg">
        @error('phone')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Campo: Correo Electrónico -->
    <div>
        <label class="block font-semibold mb-2">Correo Electrónico *</label>
        <input type="email"
               name="email"
               placeholder="tu@email.com"
               required
               class="w-full px-4 py-3 border rounded-lg">
        @error('email')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Campo: Mensaje -->
    <div>
        <label class="block font-semibold mb-2">Tu Mensaje *</label>
        <textarea name="message"
                  placeholder="Escríbenos tu mensaje aquí..."
                  rows="5"
                  required
                  class="w-full px-4 py-3 border rounded-lg resize-none">
        </textarea>
        @error('message')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botón Submit -->
    <button type="submit"
            class="w-full text-white py-4 rounded-lg font-semibold text-lg">
        Enviar Mensaje
    </button>
</form>
```

#### Características:
- **Formulario POST:**
  - Ruta: `route('detalles.contacto.submit')`
  - Método: `DetallesController@submitContact`
  - CSRF token incluido
- **Validación Laravel:**
  - Campos requeridos: name, phone, email, message
  - Mensajes de error con `@error` directives
  - Estilo de errores: texto rojo, texto pequeño
- **Diseño:**
  - Máximo ancho: 2xl (centrado)
  - Imagen de fondo con opacidad 20%
  - Z-index para separar contenido de fondo
  - Responsive padding (p-8 → p-12 en desktop)
- **Inputs:**
  - Ancho completo con padding consistente
  - Bordes redondeados
  - Estilos usando variables CSS
  - Resize deshabilitado en textarea
- **Botón submit:**
  - Ancho completo
  - Fondo naranja
  - Padding vertical generoso

---

### 5.7 Footer
**Archivo:** `resources/views/partials/footer.blade.php`

#### Estructura (líneas 10-56):

**Grid de Columnas** (líneas 12-51):
```html
<footer class="py-12" style="background-color: var(--fondo-footer);">
    <div class="container mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8 mb-8 max-w-6xl mx-auto">

            <!-- Columna 1: Información de Contacto -->
            <div>
                <img src="{{ asset('logos/logo_horizontal.png') }}" class="h-10 mb-4">
                <h3 class="font-bold mb-3">Detalles</h3>
                <p class="text-sm mb-2">📞 (+51) 944 492 316</p>
                <p class="text-sm mb-2">✉️ contacto@detalles.com</p>
                <p class="text-sm">🕒 Lun - Dom: 9:00 - 20:00</p>
            </div>

            <!-- Columna 2: Arreglos -->
            <div>
                <h3 class="font-bold mb-4">Arreglos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/rosas-flor') }}">Rosas</a></li>
                    <li><a href="{{ url('/girasoles-flor') }}">Girasoles</a></li>
                    <li><a href="{{ url('/tulipanes-flor') }}">Tulipanes</a></li>
                    <li><a href="{{ url('/boxflor') }}">Box</a></li>
                    <li><a href="{{ url('/matrimonioflor') }}">Matrimonio</a></li>
                </ul>
            </div>

            <!-- Columna 3: Ocasiones -->
            <div>
                <h3 class="font-bold mb-4">Ocasiones</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/amor') }}">Amor</a></li>
                    <li><a href="{{ url('/aniversario') }}">Aniversario</a></li>
                    <li><a href="{{ url('/hello-kitty') }}">Hello Kitty</a></li>
                    <li><a href="{{ url('/gato') }}">Gato</a></li>
                    <li><a href="{{ url('/perro') }}">Perro</a></li>
                </ul>
            </div>

            <!-- Columna 4: Regalos -->
            <div>
                <h3 class="font-bold mb-4">Regalos</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/chocolate') }}">Chocolates</a></li>
                    <li><a href="{{ url('/pinguino') }}">Peluches</a></li>
                    <li><a href="{{ url('/stich') }}">Stitch</a></li>
                    <li><a href="{{ url('/vinera') }}">Vinera</a></li>
                    <li><a href="{{ url('/taza') }}">Tazas</a></li>
                </ul>
            </div>
        </div>

        <!-- Copyright -->
        <div class="border-t pt-8 text-center text-sm max-w-6xl mx-auto">
            <p>© 2025 Detalles y Más. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>
```

#### Estructura del Footer:

**Columna 1 - Información de Contacto:**
- Logo horizontal (altura 10)
- Nombre de la empresa
- Teléfono: (+51) 944 492 316
- Email: contacto@detalles.com
- Horario: Lun - Dom: 9:00 - 20:00

**Columna 2 - Arreglos:**
- Rosas → `/rosas-flor`
- Girasoles → `/girasoles-flor`
- Tulipanes → `/tulipanes-flor`
- Box → `/boxflor`
- Matrimonio → `/matrimonioflor`

**Columna 3 - Ocasiones:**
- Amor → `/amor`
- Aniversario → `/aniversario`
- Hello Kitty → `/hello-kitty`
- Gato → `/gato`
- Perro → `/perro`

**Columna 4 - Regalos:**
- Chocolates → `/chocolate`
- Peluches → `/pinguino`
- Stitch → `/stich`
- Vinera → `/vinera`
- Tazas → `/taza`

#### Características:
- **Layout:**
  - Grid de 4 columnas en desktop
  - Stack vertical en móvil
  - Máximo ancho 6xl (centrado)
- **Enlaces:**
  - Todos los enlaces usan `url()` con slugs directos de categorías
  - Apuntan a la ruta dinámica `/{categorySlug}`
  - Hover effect con transición suave
  - Color de texto definido por variables CSS
- **Sección de copyright:**
  - Separada con borde superior
  - Centrada
  - Padding superior de 8 unidades
- **Estilos:**
  - Fondo blanco (var(--fondo-footer))
  - Texto en gris (var(--texto-principal))
  - Títulos en marrón (var(--enlaces-titulos))

---

## 6. Modelos de Base de Datos

### 6.1 Modelo Category
**Archivo:** `app/Models/Category.php`

#### Atributos Fillables (líneas 14-26):
```php
protected $fillable = [
    'company_id',      // ID de compañía (multi-tenant)
    'parent_id',       // ID de categoría padre (para subcategorías)
    'name',            // Nombre de la categoría
    'slug',            // Slug para URLs amigables
    'description',     // Descripción de la categoría
    'color',           // Color temático
    'icon',            // Icono representativo
    'image',           // Ruta de imagen
    'order',           // Orden de visualización
    'status',          // Estado activo/inactivo (boolean)
    'created_by',      // ID del usuario creador
];
```

#### Relaciones (líneas 33-63):

**1. Company** (líneas 33-36):
```php
public function company(): BelongsTo
{
    return $this->belongsTo(Company::class);
}
```
- Cada categoría pertenece a una compañía

**2. Products** (líneas 38-41):
```php
public function products(): HasMany
{
    return $this->hasMany(Product::class);
}
```
- Una categoría tiene múltiples productos

**3. Created By** (líneas 43-46):
```php
public function createdBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```
- Registro de auditoría: usuario que creó la categoría

**4. Parent/Children (Estructura Jerárquica)** (líneas 48-63):
```php
// Categoría padre
public function parent(): BelongsTo
{
    return $this->belongsTo(Category::class, 'parent_id');
}

// Todas las subcategorías
public function children(): HasMany
{
    return $this->hasMany(Category::class, 'parent_id')->orderBy('order');
}

// Solo subcategorías activas
public function activeChildren(): HasMany
{
    return $this->hasMany(Category::class, 'parent_id')
        ->where('status', true)
        ->orderBy('order');
}
```
- Permite estructura de categorías/subcategorías ilimitada
- `activeChildren()` usado en menú de navegación

#### Scopes (líneas 66-84):

**1. Active** (líneas 66-69):
```php
public function scopeActive($query)
{
    return $query->where('status', true);
}
```
- Filtra solo categorías activas

**2. ForCompany** (líneas 71-74):
```php
public function scopeForCompany($query, $companyId)
{
    return $query->where('company_id', $companyId);
}
```
- Filtra por compañía (multi-tenancy)

**3. Parents** (líneas 76-79):
```php
public function scopeParents($query)
{
    return $query->whereNull('parent_id')->orderBy('order');
}
```
- Solo categorías principales (sin padre)

**4. Children** (líneas 81-84):
```php
public function scopeChildren($query)
{
    return $query->whereNotNull('parent_id')->orderBy('order');
}
```
- Solo subcategorías (con padre)

#### Métodos Helper (líneas 87-101):

**1. getProductsCount()** (líneas 87-90):
```php
public function getProductsCount(): int
{
    return $this->products()->count();
}
```
- Cuenta productos en la categoría

**2. isParent()** (líneas 92-95):
```php
public function isParent(): bool
{
    return $this->parent_id === null;
}
```
- Verifica si es categoría principal

**3. hasChildren()** (líneas 97-100):
```php
public function hasChildren(): bool
{
    return $this->children()->count() > 0;
}
```
- Verifica si tiene subcategorías

#### Características Especiales:
- **Soft Deletes:** Usa trait `SoftDeletes`
- **Cast:** `status` casteado a boolean
- **Ordenamiento:** Siempre ordenado por campo `order`

---

### 6.2 Modelo Product
**Archivo:** `app/Models/Product.php`

#### Atributos Fillables (líneas 14-40):
```php
protected $fillable = [
    'company_id',          // ID de compañía (multi-tenant)
    'code',                // Código interno del producto
    'name',                // Nombre del producto
    'description',         // Descripción detallada
    'image_path',          // Ruta de imagen del producto
    'product_type',        // Tipo: 'product' o 'service'
    'unit_code',           // Código de unidad de medida
    'unit_description',    // Descripción de unidad
    'unit_price',          // Precio unitario base
    'sale_price',          // Precio de venta
    'cost_price',          // Precio de costo
    'tax_type',            // Tipo de impuesto
    'tax_rate',            // Tasa impositiva
    'current_stock',       // Stock actual
    'minimum_stock',       // Stock mínimo
    'track_inventory',     // Seguimiento de inventario (boolean)
    'category_id',         // ID de categoría
    'brand_id',            // ID de marca
    'category',            // Campo legacy para compatibilidad
    'brand',               // Campo legacy para compatibilidad
    'barcode',             // Código de barras
    'status',              // Estado del producto
    'taxable',             // Si aplica impuestos (boolean)
    'for_sale',            // Si está disponible para venta (boolean)
    'created_by',          // Usuario creador
];
```

#### Casts (líneas 42-56):
```php
protected $casts = [
    'unit_price' => 'decimal:4',
    'sale_price' => 'decimal:4',
    'cost_price' => 'decimal:4',
    'tax_rate' => 'decimal:4',
    'current_stock' => 'decimal:4',
    'minimum_stock' => 'decimal:4',
    'maximum_stock' => 'decimal:4',
    'weight' => 'decimal:3',
    'track_inventory' => 'boolean',
    'taxable' => 'boolean',
    'for_sale' => 'boolean',
    'for_purchase' => 'boolean',
    'additional_attributes' => 'array',
];
```
- Decimales con 4 dígitos de precisión para precios
- Booleans para flags
- Array para atributos adicionales

#### Relaciones (líneas 59-87):

**1. Company** (líneas 59-62):
```php
public function company(): BelongsTo
{
    return $this->belongsTo(Company::class);
}
```

**2. Category** (líneas 64-67):
```php
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}
```

**3. Brand** (líneas 69-72):
```php
public function brand(): BelongsTo
{
    return $this->belongsTo(Brand::class);
}
```

**4. Created By** (líneas 74-77):
```php
public function createdBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'created_by');
}
```

**5. Invoice Details** (líneas 79-82):
```php
public function invoiceDetails(): HasMany
{
    return $this->hasMany(InvoiceDetail::class);
}
```
- Registros de ventas del producto

**6. Stocks** (líneas 84-87):
```php
public function stocks(): HasMany
{
    return $this->hasMany(Stock::class);
}
```
- Movimientos de inventario

#### Scopes (líneas 90-108):

**1. Active** (líneas 90-93):
```php
public function scopeActive($query)
{
    return $query->where('status', 'active');
}
```

**2. ForSale** (líneas 95-98):
```php
public function scopeForSale($query)
{
    return $query->where('for_sale', true);
}
```

**3. Products** (líneas 100-103):
```php
public function scopeProducts($query)
{
    return $query->where('product_type', 'product');
}
```

**4. Services** (líneas 105-108):
```php
public function scopeServices($query)
{
    return $query->where('product_type', 'service');
}
```

#### Métodos Principales:

**A. Métodos de Tipo** (líneas 111-119):
```php
public function isService(): bool
{
    return $this->product_type === 'service';
}

public function isProduct(): bool
{
    return $this->product_type === 'product';
}
```

**B. Métodos de Stock** (líneas 121-124):
```php
public function isLowStock(): bool
{
    return $this->track_inventory && $this->current_stock <= $this->minimum_stock;
}
```

**C. Métodos de Impuestos** (líneas 126-133):
```php
public function getTaxAmount(float $amount): float
{
    if (!$this->taxable || $this->tax_type === '20' || $this->tax_type === '30') {
        return 0;
    }

    return $amount * $this->tax_rate;
}
```

**D. Métodos de Imagen** (líneas 136-157):
```php
// Obtener URL de imagen
public function getImageUrl(): ?string
{
    if (!$this->image_path) {
        return null;
    }

    return \Storage::disk('public')->url($this->image_path);
}

// Verificar si tiene imagen
public function hasImage(): bool
{
    return !empty($this->image_path) && \Storage::disk('public')->exists($this->image_path);
}

// Eliminar imagen
public function deleteImage(): bool
{
    if ($this->image_path && \Storage::disk('public')->exists($this->image_path)) {
        return \Storage::disk('public')->delete($this->image_path);
    }

    return true;
}
```

**E. Métodos de Código de Barras** (líneas 160-200):
```php
// Generar código único
public function generateUniqueBarcode(): string
{
    do {
        $barcode = $this->generateBarcodeNumber();
    } while (self::where('barcode', $barcode)->exists());

    return $barcode;
}

// Generar número de código
private function generateBarcodeNumber(): string
{
    // Prefijo de empresa (3 dígitos) + ID del producto (6 dígitos) + checksum (4 dígitos)
    $prefix = str_pad($this->company_id, 3, '0', STR_PAD_LEFT);
    $productId = str_pad($this->id ?? rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    return $prefix . $productId . $random;
}

// Asegurar que existe código
public function ensureBarcodeExists(): void
{
    if (empty($this->barcode)) {
        $this->barcode = $this->generateUniqueBarcode();
        $this->save();
    }
}

// Generar imagen SVG del código
public function getBarcodeImageSvg(): string
{
    if (!$this->barcode) {
        return '';
    }

    try {
        $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
        return $generator->getBarcode($this->barcode, $generator::TYPE_CODE_128);
    } catch (\Exception $e) {
        return '<text>Error: ' . $e->getMessage() . '</text>';
    }
}
```

#### Características Especiales:
- **Soft Deletes:** Usa trait `SoftDeletes`
- **Multi-tipo:** Soporta productos físicos y servicios
- **Inventario:** Sistema completo de tracking de stock
- **Impuestos:** Cálculo automático de impuestos
- **Códigos de barras:** Generación automática tipo EAN-13
- **Imágenes:** Gestión completa de archivos

---

## 7. Inyección de Datos

### ⚠️ Actualización Importante: Datos desde Controlador

**Método Actual:** Los datos de categorías se pasan directamente desde los controladores, no mediante ViewComposers.

### Archivo: `app/Http/Controllers/DetallesController.php`

#### Inyección en Página Principal (método `index()`)
```php
$menuCategories = Category::where('status', true)
    ->parents()
    ->with('activeChildren')
    ->get();

$mainCategories = $menuCategories;

return view('index', compact('menuCategories', 'mainCategories'));
```

**Variables Pasadas:**
- `$menuCategories` - Usado por `partials.header` para el menú de navegación
- `$mainCategories` - Usado por `partials.categories` para la sección de categorías

**Ventajas de este enfoque:**
- Control explícito sobre qué datos se pasan a cada vista
- Más fácil de debuggear y seguir el flujo de datos
- No hay "magia" de ViewComposers ejecutándose en background
- Reutilización de la misma consulta para ambas variables

---

#### Inyección en Página de Categoría (método `showCategory()`)
```php
$menuCategories = Category::where('status', true)
    ->parents()
    ->with('activeChildren')
    ->get();

return view('category', [
    'category' => $category,
    'products' => $products,
    'menuCategories' => $menuCategories,
]);
```

**Variables Pasadas:**
- `$category` - Categoría actual con sus relaciones
- `$products` - Productos de la categoría (lógica inteligente padre/hijo)
- `$menuCategories` - Para mantener el menú de navegación consistente

---

### Nota Histórica: ViewComposers

**Anteriormente** el proyecto usaba ViewComposers en `AppServiceProvider.php` (líneas 51-73) para inyectar datos automáticamente. Esta práctica ha sido **reemplazada** por inyección explícita desde controladores para mayor claridad y control.

Si en el futuro se necesita reactivar ViewComposers globales para datos que se usan en TODAS las vistas, el código anterior está disponible en `AppServiceProvider.php` pero actualmente **comentado o removido**.

---

## 8. Estilos CSS

### Archivo: `public/css/styles.css`

#### Variables CSS (líneas 19-41):
```css
:root {
    /* Fondos */
    --fondo-principal: #fff6f7;          /* Rosa muy claro */
    --fondo-footer: #ffffff;             /* Blanco */
    --fondo-categorias: #ffffff;         /* Blanco */
    --fondo-input: #ffffff;              /* Blanco */

    /* Textos */
    --texto-principal: #6e6d76;          /* Gris medio */
    --texto-input: #6e6d76;              /* Gris medio */
    --texto-categorias: #5b1f1f;         /* Marrón oscuro */
    --enlaces-titulos: #5b1f1f;          /* Marrón oscuro */

    /* Colores de marca */
    --naranja: #ff9900;                   /* Naranja principal */
    --azul-claro: #1ea0c3;               /* Azul claro */
    --azul-primario: #007cba;            /* Azul oscuro */
    --rojo-intenso: #cc4545;             /* Rojo para ofertas */

    /* Precios */
    --precio-original: #999999;          /* Gris claro tachado */
    --precio-actual: #000000;            /* Negro */
    --etiqueta-oferta: #cc4545;          /* Rojo */

    /* Bordes */
    --borde-input: #cccccc;              /* Gris claro */
    --borde-activo: #007cba;             /* Azul al focus */
    --borde-categorias: #dddddd;         /* Gris muy claro */

    /* Sombras */
    --sombra-ligera: rgba(0, 0, 0, 0.1) 0px 0px 20px 0px;
    --sombra-media: rgba(0, 0, 0, 0.2) 0px 4px 10px -2px;
    --sombra-fuerte: rgba(0, 0, 0, 0.2) 0px 8px 16px -8px;
}
```

#### Estilos de Imágenes (líneas 2-16):
```css
/* Estilo base de imágenes */
img {
    background-color: #f0f0f0;           /* Color mientras carga */
    min-height: 100px;                    /* Altura mínima */
}

/* Lazy loading */
.lazy-load {
    opacity: 0;
    transition: opacity 0.3s;
}

.lazy-load.loaded {
    opacity: 1;
}
```

**Propósito:**
- Fondo gris mientras las imágenes cargan
- Transición suave al cargar (fade-in)
- Altura mínima para evitar saltos de layout

---

## 9. JavaScript

### Script de Lazy Loading (Layout Principal)

#### Archivo: `resources/views/layouts/app.blade.php` (líneas 30-66)

```javascript
document.addEventListener('DOMContentLoaded', function () {
    // ===== LAZY LOADING DE IMÁGENES =====
    const lazyImages = document.querySelectorAll('.lazy-load');

    // Configurar Intersection Observer
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });

    // Observar todas las imágenes lazy
    lazyImages.forEach(img => {
        imageObserver.observe(img);
    });

    // ===== MANEJO DE ERRORES DE CARGA =====
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', function () {
            // Evitar loop infinito con placeholder
            if (!this.src.includes('via.placeholder.com')) {
                const altText = this.alt || 'Imagen no disponible';
                const width = this.getAttribute('width') || '600';
                const height = this.getAttribute('height') || '400';
                this.src = `https://via.placeholder.com/${width}x${height}?text=${encodeURIComponent(altText)}`;
            }
        });
    });
});
```

**Funcionalidad:**

1. **Lazy Loading:**
   - Detecta cuando imagen entra en viewport
   - Agrega clase `.loaded` para fade-in
   - Deja de observar después de cargar

2. **Manejo de Errores:**
   - Detecta errores de carga con evento `error`
   - Reemplaza con placeholder dinámico
   - Evita loop infinito verificando URL

---

### Script de Swiper (Categories Section)

#### Archivo: `resources/views/partials/categories.blade.php` (líneas 90-129)

```javascript
document.addEventListener('DOMContentLoaded', function () {
    const totalSlides = {{ ($mainCategories ?? collect())->count() }};

    const swiper = new Swiper('.categoriesSwiper', {
        // Configuración básica
        slidesPerView: 2,
        spaceBetween: 20,
        loop: totalSlides > 6,                  // Solo si hay más de 6
        centeredSlides: totalSlides <= 4,       // Centrar si hay 4 o menos

        // Autoplay condicional
        autoplay: totalSlides > 4 ? {
            delay: 3000,
            disableOnInteraction: false,
        } : false,

        // Paginación
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },

        // Navegación
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },

        // Breakpoints responsivos
        breakpoints: {
            640: {                                // Móvil grande
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {                                // Tablet
                slidesPerView: totalSlides >= 3 ? 3 : 2,
                spaceBetween: 24,
            },
            1024: {                               // Desktop
                slidesPerView: totalSlides >= 4 ? 3 : totalSlides,
                spaceBetween: 24,
            },
        },
    });
});
```

**Funcionalidad:**

1. **Configuración Dinámica:**
   - Lee cantidad de categorías desde Blade
   - Ajusta configuración según cantidad

2. **Loop:**
   - Solo si hay más de 6 categorías
   - Evita loop vacío con pocas slides

3. **Centrado:**
   - Centra slides si hay 4 o menos
   - Mejor visualización con pocas categorías

4. **Autoplay:**
   - Solo activo con más de 4 categorías
   - Continúa después de interacción

5. **Responsive:**
   - Ajusta cantidad de slides por viewport
   - Adapta espaciado entre slides

---

## 10. Flujo de Datos Completo

### Diagrama de Flujo:

```
1. USUARIO ACCEDE
   ↓
   http://facturacion.test/detalles

2. RUTA (routes/web.php)
   ↓
   GET /detalles → DetallesController@index

3. CONTROLADOR (DetallesController.php)
   ↓
   • Carga categorías activas principales con subcategorías
   • Query: Category::where('status', true)->parents()->with('activeChildren')
   • Asigna $menuCategories y $mainCategories
   ↓
   return view('index', compact('menuCategories', 'mainCategories'))

4. LAYOUT (layouts/app.blade.php)
   ↓
   • Carga CSS (TailwindCSS + styles.css)
   • Carga Swiper CSS
   • Incluye partials.header (recibe $menuCategories)
   • Renderiza @yield('content')
   • Incluye partials.footer
   • Carga Swiper JS
   • Ejecuta script de lazy loading

5. VISTA PRINCIPAL (index.blade.php)
   ↓
   @extends('layouts.app')
   @section('content')
      ↓
      • @include('partials.hero')
      • @include('partials.benefits')
      • @include('partials.categories')       → Usa $mainCategories
      • @include('partials.featured-products')
      • @include('partials.contact-form')
   @endsection

6. RENDERIZADO FINAL
   ↓
   HTML completo enviado al navegador

7. EJECUCIÓN EN NAVEGADOR
   ↓
   • TailwindCSS aplica estilos
   • Variables CSS personalizan colores
   • Swiper inicializa carrusel de categorías
   • IntersectionObserver activa lazy loading
   • Event listeners manejan errores de imágenes
```

---

### Flujo de Datos por Componente:

#### HEADER:
```
DetallesController@index (líneas 18-21)
  → Query: Category::where('status', true)->parents()->with('activeChildren')
  → Variable: $menuCategories
  → Vista: partials.header
  → Uso: Menú de navegación con dropdowns de subcategorías
```

#### CATEGORIES:
```
DetallesController@index (línea 23)
  → Variable: $mainCategories (misma colección que $menuCategories)
  → Vista: partials.categories
  → Lógica condicional:
     • Si count <= 4 → Grid estático
     • Si count > 4 → Swiper slider con autoplay
  → Script: Inicialización dinámica de Swiper (líneas 90-129)
```

#### CONTACT FORM:
```
Usuario completa formulario
  → POST: /detalles/contacto
  → Controlador: DetallesController@submitContact (líneas 69-83)
  → Validación: name, phone, email, message (todos required)
  → Redirección: route('contacto') con mensaje de éxito
  → Nota: Sin envío real de email (implementación pendiente)
```

#### CATEGORY PAGE:
```
Usuario click en categoría
  → GET: /{categorySlug}
  → Controlador: DetallesController@showCategory (líneas 28-67)
  → Query: Category::where('slug', $slug)
            ->with(['products', 'parent.activeChildren', 'activeChildren'])
  → Lógica inteligente:
     • Si es categoría padre → Productos de padre + todas subcategorías
     • Si es subcategoría → Solo productos de esa categoría
  → Vista: category.blade.php (no index.blade.php)
  → Datos: $category, $products, $menuCategories
```

---

## 11. Assets y Recursos

### Imágenes Requeridas:

| Asset | Ubicación | Uso |
|-------|-----------|-----|
| logo_horizontal.png | `public/logos/` | Header y Footer |
| herocontac1.jpg | `public/logos/` | Hero section - Imagen de contacto |
| herosection.png | `public/logos/` | Hero section - Imagen principal |
| contact_form.jpg | `public/logos/` | Contact form - Fondo |
| no-image.png | `public/images/` | Placeholder para categorías sin imagen |

### Categorías (Imágenes dinámicas):
- Almacenadas en: `storage/app/public/`
- Accedidas vía: `asset('storage/' . $category->image)`
- Fallback: `asset('images/no-image.png')`

### Productos Destacados (URLs externas):
- Fuente: Unsplash API
- Lazy loading habilitado
- Fallback a `via.placeholder.com`

---

## 12. Dependencias Externas

### CSS:
- **TailwindCSS**: v3+ (CDN)
- **Swiper**: v11 (CDN)

### JavaScript:
- **Swiper JS**: v11 (CDN)
- **IntersectionObserver**: API nativa del navegador

### PHP:
- **Laravel**: 11-12
- **Filament**: 3
- **Livewire**: 3

---

## 13. Rutas y Enlaces

### Named Routes Utilizadas:

#### En Header (actualizado):
```php
route('detalles.index')        // Inicio → /detalles
#nosotros                       // Anchor link interno
#buscar                         // Anchor link (placeholder)
#usuario                        // Anchor link (placeholder)
#carrito                        // Anchor link (placeholder)
```

#### En Hero:
```php
#productos                      // Anchor link a sección de categorías
#contacto                       // Anchor link a formulario de contacto
```

#### En Contact Form:
```php
route('detalles.contacto.submit')   // POST /detalles/contacto
```

---

### URLs Dinámicas de Categorías (Footer):

Todos los enlaces del footer usan `url()` con slugs que apuntan a la ruta dinámica `/{categorySlug}`:

#### Arreglos (Columna 2):
```php
url('/rosas-flor')              // → DetallesController@showCategory
url('/girasoles-flor')
url('/tulipanes-flor')
url('/boxflor')
url('/matrimonioflor')
```

#### Ocasiones (Columna 3):
```php
url('/amor')
url('/aniversario')
url('/hello-kitty')
url('/gato')
url('/perro')
```

#### Regalos (Columna 4):
```php
url('/chocolate')
url('/pinguino')                // Peluches
url('/stich')                   // Stitch
url('/vinera')
url('/taza')
```

---

### Navegación de Categorías:

Todos los enlaces de categorías del menú y sección de categorías se generan dinámicamente:

```blade
@foreach($menuCategories as $category)
    <a href="{{ url('/' . $category->slug) }}">
        {{ $category->name }}
    </a>
@endforeach
```

**Flujo:** `url('/slug')` → `Route::get('/{categorySlug}')` → `DetallesController@showCategory`

---

### Nota Importante:

- **Named routes:** Solo se usan para rutas específicas del sistema (inicio, contacto submit)
- **URL dinámicas:** Categorías usan `url()` con slugs desde base de datos
- **Anchor links:** Links internos a secciones de la misma página (#productos, #contacto, etc.)
- **Slugs válidos:** Deben existir en tabla `categories` con `status = true`

---

## 14. Características de Performance

### Optimizaciones Implementadas:

1. **Lazy Loading de Imágenes:**
   - Carga diferida con IntersectionObserver
   - Reduce peso inicial de página
   - Mejora First Contentful Paint

2. **Eager Loading de Relaciones:**
   - `->with(['activeChildren'])` en categorías
   - Evita problema N+1
   - Reduce cantidad de queries

3. **CDN para Librerías:**
   - TailwindCSS y Swiper desde CDN
   - Aprovecha caché del navegador
   - Reduce carga del servidor

4. **Imágenes con Fallback:**
   - Manejo de errores con placeholder
   - Evita imágenes rotas
   - Mejor UX

5. **Sticky Header:**
   - Header fijo al hacer scroll
   - No afecta performance
   - Mejora navegación

6. **Inyección Eficiente de Datos:**
   - Datos cargados una vez en el controlador
   - Misma colección reutilizada para múltiples vistas ($menuCategories = $mainCategories)
   - Evita consultas duplicadas

---

## 15. Consideraciones de Seguridad

### Implementadas:

1. **CSRF Token:**
   - `@csrf` en formulario de contacto
   - Protección contra Cross-Site Request Forgery

2. **Validación de Entrada:**
   - Validación en `submitContact()`
   - Sanitización automática de Laravel
   - Prevención de XSS

3. **Soft Deletes:**
   - Categorías y productos no se borran permanentemente
   - Auditoría de cambios

4. **Autorización:**
   - Campo `created_by` en modelos
   - Tracking de usuarios creadores

5. **Queries con Where:**
   - Solo datos activos (`status = true`)
   - Prevención de exposición de datos inactivos

### Recomendaciones Pendientes:

1. **Rate Limiting:**
   - Limitar envíos de formulario de contacto
   - Prevenir spam

2. **Sanitización de Inputs:**
   - Validar y sanitizar slugs en URL
   - Prevenir inyección SQL (aunque Eloquent ya protege)

3. **Autenticación:**
   - Proteger rutas de administración
   - Middleware de autenticación

---

## 16. Mejoras Potenciales

### Funcionalidad:

1. **Sistema de Búsqueda:**
   - Búsqueda de productos por nombre/categoría
   - Filtros avanzados

2. **Carrito de Compras:**
   - Implementar funcionalidad real del carrito
   - Contador dinámico de items

3. **Productos Destacados Dinámicos:**
   - Cargar desde BD en lugar de hardcoded
   - Campo `featured` en modelo Product

4. **Sistema de Envío de Emails:**
   - Implementar envío real en `submitContact()`
   - Notificaciones por email

5. **Wishlist:**
   - Lista de deseos para usuarios
   - Guardado persistente

### Performance:

1. **Caché de Categorías:**
   - Cachear queries de ViewComposers
   - Invalidar al actualizar categorías

2. **Imágenes Optimizadas:**
   - Generación automática de thumbnails
   - Formato WebP para menor peso

3. **Lazy Loading de Scripts:**
   - Cargar Swiper solo si hay >4 categorías
   - Reducir JS inicial

### UX:

1. **Menú Hamburguesa:**
   - Navegación móvil funcional
   - Actualmente oculto en móvil

2. **Breadcrumbs:**
   - Navegación jerárquica
   - Mejor orientación del usuario

3. **Testimonios Reales:**
   - Sección de reviews de clientes
   - Aumenta confianza

4. **Chat en Vivo:**
   - WhatsApp integrado
   - Soporte instantáneo

---

## 17. Testing Recomendado

### Tests Unitarios:

```php
// tests/Unit/CategoryTest.php
test('category has many products')
test('category can have children')
test('active scope returns only active categories')

// tests/Unit/ProductTest.php
test('product belongs to category')
test('product calculates tax correctly')
test('product generates unique barcode')
```

### Tests de Feature:

```php
// tests/Feature/DetallesControllerTest.php
test('index page loads successfully')
test('category page shows products')
test('contact form validates required fields')
test('contact form redirects with success message')

// tests/Feature/ViewComposerTest.php
test('header receives menu categories')
test('categories section receives main categories')
```

### Tests de Integración:

```php
// tests/Integration/NavigationTest.php
test('user can navigate from home to category')
test('user can submit contact form')
test('user can view product details')
```

---

## 18. Documentación de Variables CSS

### Paleta de Colores:

| Variable | Valor | Uso |
|----------|-------|-----|
| --fondo-principal | #fff6f7 | Background del body |
| --fondo-footer | #ffffff | Header, footer, cards |
| --texto-principal | #6e6d76 | Texto general |
| --enlaces-titulos | #5b1f1f | Títulos y enlaces |
| --naranja | #ff9900 | Color principal de marca |
| --azul-claro | #1ea0c3 | Acentos secundarios |
| --azul-primario | #007cba | Hover states |
| --rojo-intenso | #cc4545 | Etiquetas de oferta |
| --precio-actual | #000000 | Precios de venta |
| --borde-categorias | #dddddd | Bordes sutiles |

### Uso en Código:

```html
<!-- Inline styles con variables CSS -->
<div style="background-color: var(--fondo-principal);">
<p style="color: var(--texto-principal);">
<button style="background-color: var(--naranja);">
```

---

## 19. Checklist de Verificación

### Para Despliegue:

- [ ] Todas las imágenes en `public/logos/` existen
- [ ] Placeholder `no-image.png` está presente
- [ ] Todas las named routes están definidas en `routes/web.php`
- [ ] Variables de entorno configuradas (`.env`)
- [ ] Migraciones ejecutadas (`php artisan migrate`)
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Seeders ejecutados para categorías de prueba
- [ ] Caché de configuración limpiado (`php artisan config:clear`)
- [ ] Assets compilados (`npm run build`)
- [ ] Permisos de storage configurados (755/644)

### Para Testing:

- [ ] Crear categorías de prueba con imágenes
- [ ] Verificar menú desplegable con subcategorías
- [ ] Probar formulario de contacto con datos válidos/inválidos
- [ ] Verificar lazy loading de imágenes al hacer scroll
- [ ] Probar slider de categorías (>4 categorías)
- [ ] Verificar responsive design en diferentes dispositivos
- [ ] Probar navegación entre páginas
- [ ] Verificar fallback de imágenes rotas

---

## 20. Conclusión

### Resumen de la Estructura:

La página `/detalles` es una landing page completa para una tienda de regalos y flores, construida con Laravel y componentes modernos de frontend. Utiliza:

- **Arquitectura MVC** de Laravel
- **Inyección de datos desde controlador** (enfoque explícito vs ViewComposers)
- **Blade templating** con sistema de layouts y partials
- **TailwindCSS** para estilos utility-first
- **Swiper JS** para carruseles responsivos
- **Lazy Loading** con IntersectionObserver
- **Variables CSS** para theming consistente

### Cambios Importantes Implementados:

🆕 **Inyección explícita de datos** - Datos pasados directamente desde controlador
🆕 **Vista específica de categoría** - `category.blade.php` para páginas de categorías
🆕 **Lógica inteligente de productos** - Categorías padre muestran productos de subcategorías
🆕 **Anchor links** - Navegación interna con #productos, #contacto
🆕 **URLs dinámicas** - Footer usa `url()` con slugs desde base de datos

### Puntos Fuertes:

✅ Diseño responsivo completo
✅ Performance optimizado con lazy loading
✅ Código modular y reutilizable
✅ Datos dinámicos desde base de datos
✅ Validación de formularios
✅ Manejo de errores de imágenes
✅ Soft deletes para auditoría
✅ Lógica de productos padre/hijo
✅ Reutilización eficiente de queries

### Áreas de Mejora:

⚠️ Productos destacados hardcoded
⚠️ Formulario de contacto sin envío real
⚠️ Carrito sin funcionalidad
⚠️ Falta menú móvil hamburguesa
⚠️ Falta sistema de búsqueda
⚠️ Falta caché de categorías

---

**Última actualización:** 30 de septiembre de 2025 (Revisión 2)
**Versión de Laravel:** 11-12
**Versión de Filament:** 3
**Cambios en esta revisión:**
- Actualizado controlador con inyección explícita de datos
- Agregada lógica inteligente de productos padre/hijo
- Actualizados enlaces del footer con slugs reales
- Documentada vista category.blade.php
- Eliminada dependencia de ViewComposers

**Autor de la documentación:** Claude Code (Análisis automático)