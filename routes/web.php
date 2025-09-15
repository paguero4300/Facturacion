<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoicePdfController;

Route::get('/', function () {
    return view('welcome');
});

// Rutas para PDFs de facturas
Route::prefix('invoices')->name('invoices.')->group(function () {
    Route::get('{invoice}/pdf/download', [InvoicePdfController::class, 'download'])->name('pdf.download');
    Route::get('{invoice}/pdf/view', [InvoicePdfController::class, 'view'])->name('pdf.view');
    Route::get('{invoice}/pdf/preview', [InvoicePdfController::class, 'preview'])->name('pdf.preview');
    Route::get('{invoice}/pdf/temp-url', [InvoicePdfController::class, 'temporaryUrl'])->name('pdf.temp-url');
    Route::post('{invoice}/pdf/store', [InvoicePdfController::class, 'store'])->name('pdf.store');
    Route::get('{invoice}/ticket/download', [InvoicePdfController::class, 'ticket'])->name('ticket.download');
    Route::get('{invoice}/ticket/view', [InvoicePdfController::class, 'ticketView'])->name('ticket.view');
    Route::post('pdf/download-multiple', [InvoicePdfController::class, 'downloadMultiple'])->name('pdf.download-multiple');
});
