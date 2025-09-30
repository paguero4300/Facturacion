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

// Ruta dinámica para categorías (debe estar antes de las rutas específicas)
Route::get('/{categorySlug}', [DetallesController::class, 'showCategory'])->name('category.show');

// Rutas nombradas para usar en las plantillas Blade
Route::get('/inicio', function () {
    return redirect()->route('detalles.index');
})->name('inicio');

Route::get('/nosotros', function () {
    return redirect()->route('detalles.index');
})->name('nosotros');

Route::get('/ocasiones', function () {
    return redirect()->route('detalles.index');
})->name('ocasiones');

Route::get('/arreglos', function () {
    return redirect()->route('detalles.index');
})->name('arreglos');

Route::get('/regalos', function () {
    return redirect()->route('detalles.index');
})->name('regalos');

Route::get('/festivos', function () {
    return redirect()->route('detalles.index');
})->name('festivos');

Route::get('/productos', function () {
    return redirect()->route('detalles.index');
})->name('productos');

Route::get('/contacto', function () {
    return redirect()->route('detalles.index');
})->name('contacto');

// Rutas para funcionalidades adicionales
Route::get('/buscar', function () {
    return redirect()->route('detalles.index');
})->name('buscar');

Route::get('/usuario', function () {
    return redirect()->route('detalles.index');
})->name('usuario');

Route::get('/carrito', function () {
    return redirect()->route('detalles.index');
})->name('carrito');

// Rutas para subcategorías
Route::get('/amor', function () {
    return redirect()->route('detalles.index');
})->name('amor');

Route::get('/aniversario', function () {
    return redirect()->route('detalles.index');
})->name('aniversario');

Route::get('/cumpleanos', function () {
    return redirect()->route('detalles.index');
})->name('cumpleanos');

Route::get('/graduacion', function () {
    return redirect()->route('detalles.index');
})->name('graduacion');

Route::get('/nacimiento', function () {
    return redirect()->route('detalles.index');
})->name('nacimiento');

Route::get('/rosas', function () {
    return redirect()->route('detalles.index');
})->name('rosas');

Route::get('/girasoles', function () {
    return redirect()->route('detalles.index');
})->name('girasoles');

Route::get('/flores-mixtas', function () {
    return redirect()->route('detalles.index');
})->name('flores-mixtas');

Route::get('/lirios', function () {
    return redirect()->route('detalles.index');
})->name('lirios');

Route::get('/tulipanes', function () {
    return redirect()->route('detalles.index');
})->name('tulipanes');

Route::get('/peluches', function () {
    return redirect()->route('detalles.index');
})->name('peluches');

Route::get('/chocolates', function () {
    return redirect()->route('detalles.index');
})->name('chocolates');

Route::get('/desayunos', function () {
    return redirect()->route('detalles.index');
})->name('desayunos');

Route::get('/joyas', function () {
    return redirect()->route('detalles.index');
})->name('joyas');

Route::get('/perfumes', function () {
    return redirect()->route('detalles.index');
})->name('perfumes');

Route::get('/san-valentin', function () {
    return redirect()->route('detalles.index');
})->name('san-valentin');

Route::get('/dia-madre', function () {
    return redirect()->route('detalles.index');
})->name('dia-madre');

Route::get('/dia-padre', function () {
    return redirect()->route('detalles.index');
})->name('dia-padre');

Route::get('/navidad', function () {
    return redirect()->route('detalles.index');
})->name('navidad');

Route::get('/ano-nuevo', function () {
    return redirect()->route('detalles.index');
})->name('ano-nuevo');

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
