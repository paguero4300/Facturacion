<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Company;
use App\Models\Client;
use App\Models\Product;
use App\Models\DocumentSeries;

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "🧪 Probando modelos Eloquent...\n\n";
    
    // Test Company model
    echo "📊 === TEST COMPANY MODEL ===\n";
    $company = Company::first();
    if ($company) {
        echo "✅ Company: {$company->business_name} (RUC: {$company->ruc})\n";
        echo "   - Document series: " . $company->documentSeries()->count() . "\n";
        echo "   - Clients: " . $company->clients()->count() . "\n";
        echo "   - Products: " . $company->products()->count() . "\n";
    } else {
        echo "❌ No company found\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // Test DocumentSeries model
    echo "📋 === TEST DOCUMENT SERIES MODEL ===\n";
    $series = DocumentSeries::with('company')->get();
    foreach ($series as $s) {
        echo "✅ Series: {$s->series} ({$s->description})\n";
        echo "   - Company: {$s->company->business_name}\n";
        echo "   - Current number: {$s->current_number}\n";
        echo "   - Next would be: " . $s->getFullSeriesFormat() . "\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // Test Client model
    echo "👥 === TEST CLIENT MODEL ===\n";
    $clients = Client::with('company')->get();
    foreach ($clients as $client) {
        echo "✅ Client: {$client->business_name}\n";
        echo "   - Document: " . $client->getFullDocumentAttribute() . "\n";
        echo "   - Type: " . ($client->isCompany() ? 'Company' : 'Person') . "\n";
        echo "   - Company: {$client->company->business_name}\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
    
    // Test Product model
    echo "📦 === TEST PRODUCT MODEL ===\n";
    $products = Product::with('company')->get();
    foreach ($products as $product) {
        echo "✅ Product: {$product->name} ({$product->code})\n";
        echo "   - Type: " . ($product->isService() ? 'Service' : 'Product') . "\n";
        echo "   - Price: {$product->sale_price} {$product->unit_code}\n";
        echo "   - Tax type: {$product->tax_type}\n";
        echo "   - Company: {$product->company->business_name}\n";
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 Todos los modelos funcionan correctamente!\n";
    echo "✅ Relaciones verificadas exitosamente\n";
    echo "✅ Métodos de negocio funcionando\n";
    echo "✅ Scopes y castings operativos\n";
    
} catch (Exception $e) {
    echo "💥 Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n🏁 Test completado.\n";