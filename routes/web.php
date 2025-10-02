<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\DetallesController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentEvidenceController;
use App\Filament\Pages\Pos;
use App\Services\ProductTemplateService;

// Página principal en la ruta raíz
Route::get('/', [DetallesController::class, 'index'])->name('home');

// Formulario de contacto
Route::post('/contacto', [DetallesController::class, 'submitContact'])->name('contact.submit');

// Redirección 301 para compatibilidad con URLs heredadas
Route::get('/detalles', function () {
    return redirect('/', 301);
});
Route::post('/detalles/contacto', function () {
    return redirect('/contacto', 301);
});

// ====================================
// RUTAS DEL CARRITO DE COMPRAS (E-COMMERCE)
// ====================================

// Tienda (Public)
Route::prefix('tienda')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');
    Route::get('/producto/{id}', [ShopController::class, 'show'])->name('product');
});

// Carrito (Public)
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/agregar', [CartController::class, 'add'])->name('add');
    Route::patch('/actualizar', [CartController::class, 'update'])->name('update');
    Route::delete('/eliminar/{productId}', [CartController::class, 'remove'])->name('remove');
});

// Checkout (Permite invitados y usuarios registrados)
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/procesar', [CheckoutController::class, 'process'])->name('process');
    Route::get('/confirmacion/{invoice}', [CheckoutController::class, 'confirmation'])->name('confirmation');
    Route::get('/horarios-disponibles', [CheckoutController::class, 'getAvailableTimeSlots'])->name('available-timeslots');
});

// Mis Pedidos (Requiere autenticación)
Route::middleware('auth')->get('/mis-pedidos', [CheckoutController::class, 'myOrders'])->name('account.orders');

// ====================================
// RUTAS DE COMPROBANTES DE PAGO (SEGURAS)
// ====================================
Route::get('/payment-evidence/{invoice}', [PaymentEvidenceController::class, 'show'])->name('payment-evidence.show');

// ====================================
// RUTAS DE AUTENTICACIÓN (BREEZE)
// ====================================
require __DIR__.'/auth.php';

// Ruta dinámica para categorías - maneja todas las URLs de categorías automáticamente
// IMPORTANTE: Esta debe estar al final para no capturar las rutas específicas
Route::get('/{categorySlug}', [DetallesController::class, 'showCategory'])->name('category.show');

// Rutas AJAX para búsqueda de clientes en POS
Route::post('/admin/pos/search-client', function (\Illuminate\Http\Request $request) {
    $pos = new Pos();
    $result = $pos->searchClient($request->input('document_number'));
    return response()->json($result);
})->middleware(['web', 'auth']);

// Ruta para descargar plantilla de productos Excel
Route::get('/admin/products/download-template', function () {
    return ProductTemplateService::generateExcelTemplate();
})->middleware(['web', 'auth'])->name('products.download-template');

// Rutas para PDFs de facturas
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('{invoice}/pdf/download', [InvoicePdfController::class, 'download'])->name('pdf.download');
    Route::get('{invoice}/pdf/view', [InvoicePdfController::class, 'view'])->name('pdf.view');
    Route::get('{invoice}/pdf/preview', [InvoicePdfController::class, 'preview'])->name('pdf.preview');
    Route::get('{invoice}/pdf/temp-url', [InvoicePdfController::class, 'temporaryUrl'])->name('pdf.temp-url');
    Route::get('{invoice}/html/preview', [InvoicePdfController::class, 'htmlPreview'])->name('html.preview');
    Route::post('{invoice}/pdf/store', [InvoicePdfController::class, 'store'])->name('pdf.store');
    Route::get('{invoice}/ticket/download', [InvoicePdfController::class, 'ticket'])->name('ticket.download');
    Route::get('{invoice}/ticket/view', [InvoicePdfController::class, 'ticketView'])->name('ticket.view');
    Route::post('pdf/download-multiple', [InvoicePdfController::class, 'downloadMultiple'])->name('pdf.download-multiple');
});
