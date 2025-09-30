<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\DetallesController;
use App\Filament\Pages\Pos;

Route::get('/', function () {
    return redirect('/admin');
});

// Rutas para la página de Detalles
Route::prefix('detalles')->name('detalles.')->group(function () {
    Route::get('/', [DetallesController::class, 'index'])->name('index');
    Route::post('/contacto', [DetallesController::class, 'submitContact'])->name('contacto.submit');
});

// Ruta dinámica para categorías - maneja todas las URLs de categorías automáticamente
Route::get('/{categorySlug}', [DetallesController::class, 'showCategory'])->name('category.show');

// Rutas AJAX para búsqueda de clientes en POS
Route::post('/admin/pos/search-client', function (\Illuminate\Http\Request $request) {
    $pos = new Pos();
    $result = $pos->searchClient($request->input('document_number'));
    return response()->json($result);
})->middleware(['web', 'auth']);

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
