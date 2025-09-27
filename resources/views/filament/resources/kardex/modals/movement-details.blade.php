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
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                    </svg>
                    Entrada de Inventario
                    @break
                @case('OUT')
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Salida de Inventario
                    @break
                @case('TRANSFER')
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    Transferencia entre Almacenes
                    @break
                @case('ADJUST')
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
                    </svg>
                    Ajuste de Inventario
                    @break
                @case('OPENING')
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                    </svg>
                    Inventario Inicial
                    @break
                @default
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                    </svg>
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
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
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
                @if($movement->product && $movement->product->code)
                    <div class="text-xs text-gray-500 mt-1">SKU: {{ $movement->product->code }}</div>
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

    <!-- Movement Summary -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-700 mb-2">Resumen del Movimiento</h4>
        <p class="text-sm text-gray-600">
            {{ $movement->getTypeLabel() }} de {{ number_format($movement->qty, 2) }} unidades
            @if($movement->fromWarehouse || $movement->toWarehouse)
                - {{ $movement->getWarehouseMovementDescription() }}
            @endif
            @if($movement->reason)
                <br><strong>Motivo:</strong> {{ $movement->reason }}
            @endif
        </p>
    </div>
</div>