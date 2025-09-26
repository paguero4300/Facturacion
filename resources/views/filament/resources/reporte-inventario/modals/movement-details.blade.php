<div class="space-y-6">
    <style>
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .detail-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
        }
        
        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-size: 0.875rem;
            color: #1f2937;
            font-weight: 500;
        }
        
        .movement-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .type-in {
            background: #dcfce7;
            color: #166534;
        }
        
        .type-out {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .type-transfer {
            background: #fef3c7;
            color: #92400e;
        }
        
        .type-adjust {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .type-opening {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        .warehouse-flow {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .warehouse-box {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            background: white;
            border-radius: 6px;
            border: 2px solid #e2e8f0;
        }
        
        .warehouse-name {
            font-weight: 600;
            color: #1f2937;
        }
        
        .warehouse-type {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .flow-arrow {
            color: #6b7280;
            font-size: 1.5rem;
        }
        
        .quantity-display {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .quantity-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .quantity-label {
            font-size: 0.875rem;
            color: #6b7280;
        }
    </style>

    <!-- Header with Movement Type -->
    <div class="text-center">
        <div class="movement-type-badge {{ 
            $movement->type === 'IN' || $movement->type === 'OPENING' ? 'type-in' : 
            ($movement->type === 'OUT' ? 'type-out' : 
            ($movement->type === 'TRANSFER' ? 'type-transfer' : 
            ($movement->type === 'ADJUST' ? 'type-adjust' : 'type-opening'))) 
        }}">
            @switch($movement->type)
                @case('IN')
                    <i class="fas fa-plus-circle"></i>
                    Entrada de Inventario
                    @break
                @case('OUT')
                    <i class="fas fa-minus-circle"></i>
                    Salida de Inventario
                    @break
                @case('TRANSFER')
                    <i class="fas fa-exchange-alt"></i>
                    Transferencia entre Almacenes
                    @break
                @case('ADJUST')
                    <i class="fas fa-edit"></i>
                    Ajuste de Inventario
                    @break
                @case('OPENING')
                    <i class="fas fa-play-circle"></i>
                    Inventario Inicial
                    @break
                @default
                    <i class="fas fa-cube"></i>
                    Movimiento de Inventario
            @endswitch
        </div>
    </div>

    <!-- Quantity Display -->
    <div class="quantity-display">
        <div class="quantity-value {{ 
            $movement->type === 'IN' || $movement->type === 'OPENING' ? 'text-green-600' : 
            ($movement->type === 'OUT' ? 'text-red-600' : 'text-blue-600') 
        }}">
            @if($movement->type === 'IN' || $movement->type === 'OPENING')
                +{{ number_format($movement->qty, 2) }}
            @elseif($movement->type === 'OUT')
                -{{ number_format($movement->qty, 2) }}
            @else
                {{ number_format($movement->qty, 2) }}
            @endif
        </div>
        <div class="quantity-label">Cantidad del Movimiento</div>
    </div>

    <!-- Warehouse Flow (for transfers) -->
    @if($movement->type === 'TRANSFER' && $movement->fromWarehouse && $movement->toWarehouse)
        <div class="warehouse-flow">
            <div class="warehouse-box">
                <div class="warehouse-name">{{ $movement->fromWarehouse->name }}</div>
                <div class="warehouse-type">Almacén Origen</div>
            </div>
            <div class="flow-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
            <div class="warehouse-box">
                <div class="warehouse-name">{{ $movement->toWarehouse->name }}</div>
                <div class="warehouse-type">Almacén Destino</div>
            </div>
        </div>
    @endif

    <!-- Movement Details -->
    <div class="detail-grid">
        <div class="detail-card">
            <div class="detail-label">Fecha y Hora</div>
            <div class="detail-value">{{ $movement->movement_date->format('d/m/Y H:i:s') }}</div>
        </div>

        <div class="detail-card">
            <div class="detail-label">Producto</div>
            <div class="detail-value">
                {{ $movement->product->name ?? 'N/A' }}
                @if($movement->product && $movement->product->sku)
                    <div class="text-xs text-gray-500 mt-1">SKU: {{ $movement->product->sku }}</div>
                @endif
            </div>
        </div>

        @if($movement->type !== 'TRANSFER')
            <div class="detail-card">
                <div class="detail-label">Almacén</div>
                <div class="detail-value">
                    {{ $movement->fromWarehouse?->name ?? $movement->toWarehouse?->name ?? 'N/A' }}
                </div>
            </div>
        @endif

        @if($movement->reason)
            <div class="detail-card">
                <div class="detail-label">Descripción/Motivo</div>
                <div class="detail-value">{{ $movement->reason }}</div>
            </div>
        @endif

        @if($movement->ref_type && $movement->ref_id)
            <div class="detail-card">
                <div class="detail-label">Documento de Referencia</div>
                <div class="detail-value">{{ $movement->ref_type }} #{{ $movement->ref_id }}</div>
            </div>
        @endif

        <div class="detail-card">
            <div class="detail-label">Usuario</div>
            <div class="detail-value">{{ $movement->user?->name ?? 'Sistema' }}</div>
        </div>

        <div class="detail-card">
            <div class="detail-label">Fecha de Registro</div>
            <div class="detail-value">{{ $movement->created_at->format('d/m/Y H:i:s') }}</div>
        </div>
    </div>

    <!-- Additional Information -->
    @if($movement->notes || $movement->batch_number || $movement->expiry_date)
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Información Adicional</h4>
            <div class="detail-grid">
                @if($movement->batch_number)
                    <div class="detail-card">
                        <div class="detail-label">Número de Lote</div>
                        <div class="detail-value">{{ $movement->batch_number }}</div>
                    </div>
                @endif

                @if($movement->expiry_date)
                    <div class="detail-card">
                        <div class="detail-label">Fecha de Vencimiento</div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($movement->expiry_date)->format('d/m/Y') }}</div>
                    </div>
                @endif

                @if($movement->notes)
                    <div class="detail-card" style="grid-column: 1 / -1;">
                        <div class="detail-label">Notas</div>
                        <div class="detail-value">{{ $movement->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Movement Summary -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Resumen del Movimiento</h4>
        <p class="text-sm text-gray-600">
            {{ $movement->getTypeDescription() }}
            @if($movement->fromWarehouse || $movement->toWarehouse)
                {{ $movement->getWarehouseMovementDescription() }}
            @endif
        </p>
    </div>
</div>