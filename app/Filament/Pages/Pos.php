<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Client;
use App\Models\Company;
use App\Models\DocumentSeries;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Modal\Actions\Action as ModalAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Log;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class Pos extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $categories;
    public $products;

    protected static ?string $title = '';
    
    protected static ?string $navigationLabel = 'POS';
    
    protected static UnitEnum|string|null $navigationGroup = 'Gestión Comercial';
    
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    
    public array $cart = [];
    
    public ?int $selectedCategory = null;
    
    public string $searchTerm = '';
    
    public string $discountType = 'none';
    
    public float $discountValue = 0;
    
    public float $calculatedDiscount = 0;
    
    public float $shippingCost = 0;
    
    public float $taxRate = 18;
    
    public float $subtotal = 0;

    public float $igv = 0;

    public float $total = 0;
    
    public ?Company $company = null;

    protected $listeners = ['refreshTable' => '$refresh'];
    
    public function updatedDiscountType(): void
    {
        $this->discountValue = 0;
        $this->calculateTotals();
    }
    
    public function updatedDiscountValue(): void
    {
        $this->calculateTotals();
    }
    
    public function updatedShippingCost(): void
    {
        $this->calculateTotals();
    }

    public static function getNavigationIcon(): ?string
    {
        return 'iconoir-shopping-bag';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function getView(): string
    {
        return 'filament.pages.pos';
    }

    public function mount(): void
    {
        // Cargar datos de la empresa
        $this->company = Company::active()->first();

        // Load categories and products once to avoid N+1 queries
        $this->categories = Category::orderBy('name')->get();
        $this->products = $this->getFilteredProducts();

        $this->data = [
            'client_id' => null,
            'document_series_id' => DocumentSeries::where('document_type', '03')->first()?->id,
            'payment_method' => 'cash',
            'currency_code' => 'PEN',
            'exchange_rate' => 1.0000,
            'cart_items' => [],
        ];

        $this->calculateTotals();
    }


    private function getFilteredProducts()
    {
        return Product::query()
            ->active()
            ->forSale()
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->get();
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    Select::make('client_id')
                        ->label('Cliente')
                        ->options(Client::active()->pluck('business_name', 'id'))
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('document_type')
                                ->label('Tipo Doc.')
                                ->default('1')
                                ->required(),
                            TextInput::make('document_number')
                                ->label('Número')
                                ->required(),
                            TextInput::make('business_name')
                                ->label('Nombre/Razón Social')
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data): int {
                            return Client::create($data)->id;
                        }),
                        
                    Select::make('document_series_id')
                        ->label('Serie')
                        ->options(DocumentSeries::pluck('series', 'id'))
                        ->required(),
                        
                    Select::make('payment_method')
                        ->label('Método de Pago')
                        ->options([
                            'cash' => 'Efectivo',
                            'card' => 'Tarjeta',
                            'transfer' => 'Transferencia',
                            'mixed' => 'Mixto',
                        ])
                        ->default('cash')
                        ->required(),
                ]),
                
            Repeater::make('cart_items')
                ->label('Productos en el Carrito')
                ->schema([
                    Hidden::make('product_id'),
                    TextInput::make('description')
                        ->label('Producto')
                        ->disabled(),
                    TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->default(1)
                        ->live()
                        ->afterStateUpdated(fn () => $this->calculateTotals()),
                    TextInput::make('unit_price')
                        ->label('Precio Unit.')
                        ->numeric()
                        ->live()
                        ->afterStateUpdated(fn () => $this->calculateTotals()),
                    TextInput::make('total')
                        ->label('Total')
                        ->disabled(),
                ])
                ->columns(5)
                ->defaultItems(0)
                ->addable(false)
                ->deletable()
                ->reorderable(false),
        ];
    }



    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->active()
                    ->forSale()
                    ->when($this->selectedCategory, fn (Builder $query) => 
                        $query->where('category_id', $this->selectedCategory)
                    )
            )
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->disk('public')
                    ->width(60)
                    ->height(60)
                    ->defaultImageUrl(url('/images/no-image.svg')),
                    
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                    
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                    
                TextColumn::make('sale_price')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable(),
                    
                TextColumn::make('current_stock')
                    ->label('Stock')
                    ->numeric(2)
                    ->color(fn (Product $record): string => 
                        $record->isLowStock() ? 'danger' : 'success'
                    ),
            ])
            ->actions([
                \Filament\Actions\Action::make('add_to_cart')
                    ->label('Agregar')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->action(function (Product $record) {
                        $this->addToCart($record);
                    }),
            ])
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategory = $categoryId;

        // Reload filtered products when category changes
        $this->products = $this->getFilteredProducts();

        // Resetear la tabla para aplicar el filtro
        $this->resetTable();

        // Emitir evento para actualizar la vista
        $this->dispatch('categoryChanged', $categoryId);

        // Mostrar notificación
        if ($categoryId) {
            $category = Category::find($categoryId);
            Notification::make()
                ->title('Categoría seleccionada')
                ->body("Mostrando productos de: {$category->name}")
                ->info()
                ->send();
        } else {
            Notification::make()
                ->title('Filtro removido')
                ->body('Mostrando todos los productos')
                ->info()
                ->send();
        }
    }

    public function addToCart($productId): void
    {
        $product = Product::find($productId);
        
        if (!$product) {
            Notification::make()
                ->title('Error')
                ->body('Producto no encontrado')
                ->danger()
                ->send();
            return;
        }
        
        $cartItems = $this->data['cart_items'] ?? [];
        
        // Buscar si el producto ya está en el carrito
        $existingIndex = null;
        foreach ($cartItems as $index => $item) {
            if ($item['product_id'] == $product->id) {
                $existingIndex = $index;
                break;
            }
        }
        
        if ($existingIndex !== null) {
            // Incrementar cantidad
            $cartItems[$existingIndex]['quantity']++;
            $cartItems[$existingIndex]['total'] = $cartItems[$existingIndex]['quantity'] * $cartItems[$existingIndex]['unit_price'];
        } else {
            // Agregar nuevo producto
            $cartItems[] = [
                'product_id' => $product->id,
                'description' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->sale_price,
                'total' => $product->sale_price,
            ];
        }
        
        $this->data['cart_items'] = $cartItems;
        $this->calculateTotals();
        
        Notification::make()
            ->title('Producto agregado al carrito')
            ->body($product->name)
            ->success()
            ->send();
    }

    public function calculateTotals(): void
    {
        $cartItems = $this->data['cart_items'] ?? [];
        $subtotal = 0;
        
        foreach ($cartItems as $index => $item) {
            $lineTotal = ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            $cartItems[$index]['total'] = $lineTotal;
            $subtotal += $lineTotal;
        }
        
        $this->data['cart_items'] = $cartItems;
        $this->subtotal = $subtotal;
        
        // Calcular descuento
        $this->calculatedDiscount = 0;
        if ($this->discountType === 'fixed') {
            $this->calculatedDiscount = min($this->discountValue, $subtotal);
        } elseif ($this->discountType === 'percentage') {
            $this->calculatedDiscount = ($subtotal * $this->discountValue) / 100;
        }
        
        // Subtotal después del descuento
        $discountedSubtotal = $subtotal - $this->calculatedDiscount;
        
        // Calcular IGV sobre el subtotal con descuento
        $this->igv = $discountedSubtotal * ($this->taxRate / 100);
        
        // Total final
        $this->total = $discountedSubtotal + $this->igv + $this->shippingCost;
    }
    
    public function increaseQuantity(int $index): void
    {
        if (isset($this->data['cart_items'][$index])) {
            $this->data['cart_items'][$index]['quantity']++;
            $this->data['cart_items'][$index]['total'] = 
                $this->data['cart_items'][$index]['quantity'] * $this->data['cart_items'][$index]['unit_price'];
            $this->calculateTotals();
        }
    }
    
    public function decreaseQuantity(int $index): void
    {
        if (isset($this->data['cart_items'][$index])) {
            if ($this->data['cart_items'][$index]['quantity'] > 1) {
                $this->data['cart_items'][$index]['quantity']--;
                $this->data['cart_items'][$index]['total'] = 
                    $this->data['cart_items'][$index]['quantity'] * $this->data['cart_items'][$index]['unit_price'];
                $this->calculateTotals();
            } else {
                $this->removeFromCart($index);
            }
        }
    }
    
    public function removeFromCart(int $index): void
    {
        if (isset($this->data['cart_items'][$index])) {
            $productName = $this->data['cart_items'][$index]['description'];
            unset($this->data['cart_items'][$index]);
            $this->data['cart_items'] = array_values($this->data['cart_items']); // Reindexar
            $this->calculateTotals();
            
            Notification::make()
                ->title('Producto eliminado')
                ->body($productName)
                ->warning()
                ->send();
        }
    }

    public function clearCart(): void
    {
        $this->data['cart_items'] = [];
        $this->discountType = 'none';
        $this->discountValue = 0;
        $this->shippingCost = 0;
        $this->calculateTotals();
        
        Notification::make()
            ->title('Carrito limpiado')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function processPayment(): void
    {
        if (empty($this->data['cart_items'])) {
            Notification::make()
                ->title('Error')
                ->body('El carrito está vacío')
                ->danger()
                ->send();
            return;
        }

        // Abrir modal de pago
        $this->dispatch('open-payment-modal');
    }

    public function processSale(array $paymentData): array
    {
        if (empty($this->data['cart_items'])) {
            Notification::make()
                ->title('Error')
                ->body('El carrito está vacío')
                ->danger()
                ->send();
            return ['success' => false, 'error' => 'El carrito está vacío'];
        }

        // Debug: Log payment data
        \Log::info('Processing sale with payment data:', $paymentData);
        \Log::info('Current totals:', [
            'subtotal' => $this->subtotal,
            'igv' => $this->igv,
            'total' => $this->total,
            'cart_items_count' => count($this->data['cart_items'] ?? [])
        ]);

        $invoiceData = null;
        DB::transaction(function () use ($paymentData, &$invoiceData) {
            // Bloquear la serie para evitar concurrencia
            $series = DocumentSeries::where('document_type', $paymentData['document_type'])
                ->where('status', 'active')
                ->where('is_default', true)
                ->lockForUpdate()
                ->first();
                
            if (!$series) {
                // Intentar obtener cualquier serie activa para el tipo de documento
                $series = DocumentSeries::where('document_type', $paymentData['document_type'])
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->first();
            }

            
            if (!$series) {
                $availableTypes = DocumentSeries::where('status', 'active')->pluck('document_type')->unique()->toArray();
                \Log::error('No series found', [
                    'requested_type' => $paymentData['document_type'],
                    'available_types' => $availableTypes,
                    'all_series' => DocumentSeries::all()->toArray()
                ]);
                throw new \Exception("No se encontró una serie configurada para el tipo de documento {$paymentData['document_type']}. Tipos disponibles: " . 
                    implode(', ', $availableTypes));
            }
            
            \Log::info('Found series:', $series->toArray());
            
            // Determinar cliente según tipo de documento
            $clientId = null;
            $clientDocumentType = null;
            $clientDocumentNumber = null;
            $clientBusinessName = 'CLIENTE VARIOS';
            
            if ($paymentData['document_type'] === '01' || (!empty($paymentData['use_specific_client']) && $paymentData['use_specific_client'] === 'true')) {
                // Factura o cliente específico para boleta/nota de venta
                if ($paymentData['document_type'] === '01' && (empty($paymentData['client_document_number']) || empty($paymentData['client_business_name']))) {
                    throw new \Exception('Para facturas se requieren los datos del cliente (documento y razón social)');
                }
                
                if (!empty($paymentData['client_document_number']) && !empty($paymentData['client_business_name'])) {
                    // Buscar o crear cliente
                    $client = Client::firstOrCreate(
                        [
                            'document_type' => $paymentData['client_document_type'],
                            'document_number' => $paymentData['client_document_number'],
                        ],
                        [
                            'company_id' => Company::first()->id,
                            'business_name' => $paymentData['client_business_name'],
                            'client_type' => 'regular',
                            'status' => 'active',
                        ]
                    );
                    
                    $clientId = $client->id;
                    $clientDocumentType = $paymentData['client_document_type'];
                    $clientDocumentNumber = $paymentData['client_document_number'];
                    $clientBusinessName = $paymentData['client_business_name'];
                } else {
                    // Usar cliente genérico si no se proporcionaron datos
                    $client = Client::where('document_number', '00000000')
                        ->where('business_name', 'CLIENTE VARIOS')
                        ->first();
                    
                    if (!$client) {
                        $client = Client::create([
                            'company_id' => Company::first()->id,
                            'document_type' => '1',
                            'document_number' => '00000000',
                            'business_name' => 'CLIENTE VARIOS',
                            'client_type' => 'regular',
                            'status' => 'active',
                        ]);
                    }
                    
                    $clientId = $client->id;
                    $clientDocumentType = $client->document_type;
                    $clientDocumentNumber = $client->document_number;
                    $clientBusinessName = $client->business_name;
                }
            } else {
                // Boleta y Nota de Venta: usar cliente genérico existente
                $client = Client::where('document_number', '00000000')
                    ->where('business_name', 'CLIENTE VARIOS')
                    ->first();
                
                if (!$client) {
                    // Crear cliente genérico si no existe
                    $client = Client::create([
                        'company_id' => Company::first()->id,
                        'document_type' => '1',
                        'document_number' => '00000000',
                        'business_name' => 'CLIENTE VARIOS',
                        'client_type' => 'regular',
                        'status' => 'active',
                    ]);
                }
                
                $clientId = $client->id;
                $clientDocumentType = $client->document_type;
                $clientDocumentNumber = $client->document_number;
                $clientBusinessName = $client->business_name;
            }
            
            // Determinar status SUNAT según tipo de documento
            $sunatStatus = match($paymentData['document_type']) {
                '09' => 'pending', // Nota de venta no va a SUNAT pero usamos pending
                '01', '03' => 'pending', // Factura y Boleta van a SUNAT
                default => 'pending'
            };
            
            // Obtener el siguiente número sin incrementar aún
            $nextNumber = $series->current_number + 1;
            $fullNumber = $series->series . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            
            // Verificar que no exista duplicado
            $existingInvoice = Invoice::where('company_id', 1)
                ->where('series', $series->series)
                ->where('number', $nextNumber)
                ->first();
                
            if ($existingInvoice) {
                // Si existe, buscar el siguiente número disponible
                $lastInvoice = Invoice::where('company_id', 1)
                    ->where('series', $series->series)
                    ->orderBy('number', 'desc')
                    ->first();
                    
                $nextNumber = $lastInvoice ? $lastInvoice->number + 1 : $series->current_number + 1;
                $fullNumber = $series->series . '-' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            }
            
            // Validar datos antes de crear
            $invoiceData = [
                'company_id' => Company::first()->id,
                'client_id' => $clientId,
                'document_series_id' => $series->id,
                'series' => $series->series,
                'number' => $nextNumber,
                'full_number' => $fullNumber,
                'document_type' => $paymentData['document_type'],
                'issue_date' => now()->format('Y-m-d'),
                'issue_time' => now()->format('H:i:s'),
                'currency_code' => 'PEN',
                'exchange_rate' => 1.0000,
                'client_document_type' => $clientDocumentType,
                'client_document_number' => $clientDocumentNumber,
                'client_business_name' => $clientBusinessName,
                'operation_type' => '0101', // Venta interna
                'subtotal' => $this->subtotal,
                'tax_exempt_amount' => 0.00,
                'unaffected_amount' => 0.00,
                'free_amount' => 0.00,
                'igv_rate' => 0.18,
                'igv_amount' => $this->igv,
                'isc_amount' => 0.00,
                'other_taxes_amount' => 0.00,
                'total_charges' => 0.00,
                'total_discounts' => 0.00,
                'global_discount' => 0.00,
                'total_amount' => $this->total,
                'paid_amount' => $this->getPaidAmount($paymentData),
                'pending_amount' => 0,
                'payment_method' => $paymentData['payment_method'],
                'payment_reference' => $paymentData['digital_reference'] ?? null,
                'payment_phone' => $paymentData['digital_phone'] ?? null,
                'payment_condition' => 'immediate',
                'credit_days' => 0,
                'sunat_status' => $sunatStatus,
                'status' => 'paid',
                'is_contingency' => false,
                'created_by' => auth()->id(),
            ];
            
            \Log::info('Creating invoice with data:', $invoiceData);
            \Log::info('Payment method details:', [
                'method' => $paymentData['payment_method'],
                'amount_received' => $paymentData['amount_received'] ?? 'not_set',
                'calculated_paid_amount' => $this->getPaidAmount($paymentData),
                'total' => $this->total
            ]);
            
            // Crear factura/boleta/nota de venta
            $invoice = Invoice::create($invoiceData);
            
            // Actualizar la serie solo después de crear exitosamente
            $series->update([
                'current_number' => $nextNumber,
                'last_used_at' => now()
            ]);
            
            \Log::info('Invoice created successfully:', ['id' => $invoice->id, 'full_number' => $invoice->full_number]);
            \Log::info('Invoice totals after creation:', [
                'subtotal' => $invoice->subtotal,
                'igv_amount' => $invoice->igv_amount,
                'total_amount' => $invoice->total_amount
            ]);
            
            // Crear detalles
            foreach ($this->data['cart_items'] as $index => $item) {
                $product = Product::find($item['product_id']);
                
                // Calcular correctamente los montos (asumiendo que unit_price incluye IGV)
                $quantity = $item['quantity'];
                $unitPriceWithIgv = $item['unit_price'];
                $unitValueWithoutIgv = $unitPriceWithIgv / 1.18; // Valor unitario sin IGV
                $grossAmount = $quantity * $unitPriceWithIgv;
                $netAmount = $quantity * $unitValueWithoutIgv; // Base imponible
                $igvAmount = $netAmount * 0.18; // IGV sobre la base imponible
                $lineTotal = $netAmount + $igvAmount; // Total de la línea
                
                \Log::info("Detail calculation for item {$index}:", [
                    'product' => $product->name,
                    'quantity' => $quantity,
                    'unit_price_with_igv' => $unitPriceWithIgv,
                    'unit_value_without_igv' => $unitValueWithoutIgv,
                    'net_amount' => $netAmount,
                    'igv_amount' => $igvAmount,
                    'line_total' => $lineTotal
                ]);
                
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'line_number' => $index + 1,
                    'product_code' => $product->code,
                    'description' => $product->name,
                    'unit_code' => $product->unit_code ?? 'NIU',
                    'quantity' => $quantity,
                    'unit_price' => $unitPriceWithIgv,
                    'unit_value' => $unitValueWithoutIgv,
                    'gross_amount' => $grossAmount,
                    'net_amount' => $netAmount,
                    'igv_base_amount' => $netAmount,
                    'tax_type' => '10', // Gravado
                    'igv_rate' => 0.18,
                    'igv_amount' => $igvAmount,
                    'total_taxes' => $igvAmount,
                    'line_total' => $lineTotal,
                ]);
                
                // Actualizar stock si es producto físico
                if ($product->track_inventory ?? false) {
                    $product->decrement('current_stock', $item['quantity']);
                }
            }
            
            // Refrescar la factura para obtener los totales calculados por el Observer
            $invoice->refresh();
            \Log::info('Invoice totals after details creation and Observer calculation:', [
                'subtotal' => $invoice->subtotal,
                'igv_amount' => $invoice->igv_amount,
                'total_amount' => $invoice->total_amount
            ]);
            
            // Capturar datos del carrito ANTES de limpiarlo
            $cartItemsForResponse = $this->data['cart_items'];
            Log::info('Cart items being sent to frontend (before clearing):', $cartItemsForResponse);
            
            // Limpiar carrito
            $this->data['cart_items'] = [];
            $this->calculateTotals();
            
            $documentName = match($paymentData['document_type']) {
                '01' => 'Factura',
                '03' => 'Boleta de Venta',
                '09' => 'Nota de Venta',
                default => 'Documento'
            };
            
            // Mensaje con información del vuelto si es efectivo
            $message = "{$documentName}: {$invoice->series}-{$invoice->number}";
            if ($paymentData['payment_method'] === 'cash' && isset($paymentData['change_amount'])) {
                $changeAmount = floatval($paymentData['change_amount']);
                if ($changeAmount > 0) {
                    $message .= "\nVuelto: S/ " . number_format($changeAmount, 2);
                }
            }
            
            Notification::make()
                ->title('Venta procesada exitosamente')
                ->body($message)
                ->success()
                ->duration(5000)
                ->send();
            
            $invoiceData = [
                'success' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'series' => $invoice->series,
                    'number' => $invoice->number,
                    'full_number' => $invoice->full_number,
                    'document_type' => $invoice->document_type,
                    'issue_date' => $invoice->issue_date->format('d/m/Y'),
                    'issue_time' => $invoice->issue_date->format('H:i'),
                    'client_name' => $invoice->client_business_name,
                    'client_document' => $invoice->client_document_number,
                    'client_document_type' => $invoice->client_document_type,
                    'subtotal' => $invoice->subtotal,
                    'igv_amount' => $invoice->igv_amount,
                    'total_amount' => $invoice->total_amount,
                    'payment_method' => $paymentData['payment_method'],
                    'change_amount' => $paymentData['change_amount'] ?? 0
                ],
                'cart_items' => $cartItemsForResponse
            ];
            
            Log::info('Complete invoice data being returned:', $invoiceData);
        });
        
        return $invoiceData ?? ['success' => false, 'error' => 'Error en la transacción'];
    }
    
    public function searchClient(string $documentNumber): array
    {
        // PASO 1: Buscar en tabla local 'clients' filtrado por company_id
        $activeCompany = Company::where('is_active', true)->first();
        
        if ($activeCompany) {
            $client = Client::where('document_number', $documentNumber)
                ->where('company_id', $activeCompany->id)
                ->where('status', 'active')
                ->first();
                
            if ($client) {
                return [
                    'success' => true,
                    'source' => 'local',
                    'client' => [
                        'id' => $client->id,
                        'document_type' => $client->document_type,
                        'document_number' => $client->document_number,
                        'business_name' => $client->business_name,
                    ],
                    'message' => '✅ Cliente encontrado en registros locales'
                ];
            }
        }
        
        // PASO 2: Si no se encuentra localmente, buscar en Factiliza
        try {
            $factilizaService = app(\App\Services\FactilizaService::class);
            $documentType = strlen($documentNumber) === 8 ? '1' : '6'; // DNI o RUC
            
            if ($documentType === '1') {
                $result = $factilizaService->consultarDni($documentNumber);
            } else {
                $result = $factilizaService->consultarRuc($documentNumber);
            }
            
            if ($result['success'] && $result['data']) {
                $data = $result['data'];
                
                return [
                    'success' => true,
                    'source' => 'factiliza',
                    'client' => [
                        'id' => null,
                        'document_type' => $documentType,
                        'document_number' => $documentNumber,
                        'business_name' => $documentType === '1' 
                            ? ($data['nombre_completo'] ?? '') 
                            : ($data['nombre_o_razon_social'] ?? ''),
                        'address' => $data['direccion'] ?? '',
                        'district' => $data['distrito'] ?? '',
                        'province' => $data['provincia'] ?? '',
                        'department' => $data['departamento'] ?? '',
                    ],
                    'message' => '🌐 Datos encontrados en Factiliza',
                    'can_save' => true
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error en búsqueda Factiliza desde POS', [
                'document_number' => $documentNumber,
                'error' => $e->getMessage()
            ]);
        }
        
        // PASO 3: No encontrado en ningún lado
        return [
            'success' => false,
            'source' => 'none',
            'client' => null,
            'message' => '⚠️ Cliente no encontrado. Puede ingresar los datos manualmente.'
        ];
    }
    
    public function saveFactilizaClientToLocal(string $documentNumber): array
    {
        try {
            $activeCompany = Company::where('is_active', true)->first();
            
            if (!$activeCompany) {
                return [
                    'success' => false,
                    'message' => 'No hay empresa activa seleccionada'
                ];
            }
            
            // Verificar si ya existe el cliente
            $existingClient = Client::where('document_number', $documentNumber)
                ->where('company_id', $activeCompany->id)
                ->first();
                
            if ($existingClient) {
                return [
                    'success' => false,
                    'message' => 'El cliente ya existe en los registros locales'
                ];
            }
            
            // Buscar datos en Factiliza
            $factilizaService = app(\App\Services\FactilizaService::class);
            $documentType = strlen($documentNumber) === 8 ? '1' : '6';
            
            if ($documentType === '1') {
                $result = $factilizaService->consultarDni($documentNumber);
            } else {
                $result = $factilizaService->consultarRuc($documentNumber);
            }
            
            if (!$result['success'] || !$result['data']) {
                return [
                    'success' => false,
                    'message' => 'No se pudieron obtener los datos de Factiliza'
                ];
            }
            
            $data = $result['data'];
            
            // Crear cliente en tabla local
            $client = Client::create([
                'company_id' => $activeCompany->id,
                'document_type' => $documentType,
                'document_number' => $documentNumber,
                'business_name' => $documentType === '1' 
                    ? ($data['nombre_completo'] ?? '') 
                    : ($data['nombre_o_razon_social'] ?? ''),
                'address' => $data['direccion'] ?? '',
                'district' => $data['distrito'] ?? '',
                'province' => $data['provincia'] ?? '',
                'department' => $data['departamento'] ?? '',
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Cliente guardado exitosamente',
                'client_id' => $client->id
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error guardando cliente de Factiliza en POS', [
                'document_number' => $documentNumber,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error interno al guardar cliente'
            ];
        }
    }
    
    private function getPaidAmount(array $paymentData): float
    {
        // Para efectivo, usar el monto recibido si está disponible
        if ($paymentData['payment_method'] === 'cash' && !empty($paymentData['amount_received'])) {
            return floatval($paymentData['amount_received']);
        }
        
        // Para todos los demás métodos de pago (tarjeta, yape, plin, transferencia, etc.)
        // se asume que se paga el total exacto
        return $this->total;
    }
}