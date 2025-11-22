{{-- Progress Indicator Component for Checkout Flow --}}
@props(['currentStep' => 1])

@php
    $steps = [
        1 => [
            'name' => 'Carrito',
            'icon' => 'fa-shopping-cart',
            'route' => 'cart.index'
        ],
        2 => [
            'name' => 'Finalizar Compra',
            'icon' => 'fa-credit-card',
            'route' => 'checkout.index'
        ],
        3 => [
            'name' => 'Pedido Completado',
            'icon' => 'fa-check-circle',
            'route' => null // No clickeable - solo se llega completando
        ]
    ];
@endphp

<div class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 py-4">
        {{-- Desktop View --}}
        <div class="hidden md:flex items-center justify-center">
            @foreach($steps as $stepNumber => $step)
                {{-- Step Container --}}
                <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                    {{-- Step Circle and Label --}}
                    <div class="flex flex-col items-center {{ $loop->last ? '' : 'flex-shrink-0' }}">
                        @php
                            // Solo hacer clickeable si es un paso anterior al actual
                            $isClickable = $stepNumber < $currentStep && $step['route'];
                            $stepClasses = 'relative flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300';
                            
                            if ($stepNumber < $currentStep) {
                                $stepClasses .= ' bg-green-500 text-white shadow-md';
                            } elseif ($stepNumber == $currentStep) {
                                $stepClasses .= ' bg-[var(--naranja)] text-white shadow-lg ring-4 ring-orange-100';
                            } else {
                                $stepClasses .= ' bg-gray-200 text-gray-400';
                            }
                            
                            if ($isClickable) {
                                $stepClasses .= ' cursor-pointer hover:scale-110 hover:shadow-xl';
                            }
                        @endphp
                        
                        {{-- Circle (clickeable o no) --}}
                        @if($isClickable)
                            <a href="{{ route($step['route']) }}" 
                               class="{{ $stepClasses }}"
                               title="Volver a {{ $step['name'] }}">
                                @if($stepNumber < $currentStep)
                                    <i class="fas fa-check text-lg"></i>
                                @else
                                    <i class="fas {{ $step['icon'] }} text-lg"></i>
                                @endif
                            </a>
                        @else
                            <div class="{{ $stepClasses }}">
                                @if($stepNumber < $currentStep)
                                    <i class="fas fa-check text-lg"></i>
                                @else
                                    <i class="fas {{ $step['icon'] }} text-lg"></i>
                                @endif
                            </div>
                        @endif
                        
                        {{-- Label --}}
                        @php
                            $labelClasses = 'mt-2 text-sm font-medium transition-all';
                            if ($stepNumber < $currentStep) {
                                $labelClasses .= ' text-green-600';
                            } elseif ($stepNumber == $currentStep) {
                                $labelClasses .= ' text-[var(--naranja)] font-bold';
                            } else {
                                $labelClasses .= ' text-gray-400';
                            }
                            
                            if ($isClickable) {
                                $labelClasses .= ' hover:underline cursor-pointer';
                            }
                        @endphp
                        
                        @if($isClickable)
                            <a href="{{ route($step['route']) }}" class="{{ $labelClasses }}">
                                {{ $step['name'] }}
                            </a>
                        @else
                            <span class="{{ $labelClasses }}">
                                {{ $step['name'] }}
                            </span>
                        @endif
                    </div>
                    
                    {{-- Connecting Line --}}
                    @if(!$loop->last)
                        <div class="flex-1 h-1 mx-4 transition-all duration-300
                            @if($stepNumber < $currentStep)
                                bg-green-500
                            @else
                                bg-gray-200
                            @endif">
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Mobile View --}}
        <div class="md:hidden">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600">
                    Paso {{ $currentStep }} de {{ count($steps) }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ $steps[$currentStep]['name'] }}
                </span>
            </div>
            
            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="bg-[var(--naranja)] h-full rounded-full transition-all duration-500 shadow-sm"
                     style="width: {{ ($currentStep / count($steps)) * 100 }}%">
                </div>
            </div>
            
            {{-- Step Icons Mobile --}}
            <div class="flex items-center justify-between mt-3">
                @foreach($steps as $stepNumber => $step)
                    @php
                        $isClickable = $stepNumber < $currentStep && $step['route'];
                    @endphp
                    
                    <div class="flex flex-col items-center flex-1">
                        @if($isClickable)
                            <a href="{{ route($step['route']) }}"
                               class="w-8 h-8 rounded-full flex items-center justify-center text-xs transition-all
                                @if($stepNumber < $currentStep)
                                    bg-green-500 text-white
                                @elseif($stepNumber == $currentStep)
                                    bg-[var(--naranja)] text-white
                                @else
                                    bg-gray-200 text-gray-400
                                @endif hover:scale-110">
                                @if($stepNumber < $currentStep)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $step['icon'] }}"></i>
                                @endif
                            </a>
                        @else
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs transition-all
                                @if($stepNumber < $currentStep)
                                    bg-green-500 text-white
                                @elseif($stepNumber == $currentStep)
                                    bg-[var(--naranja)] text-white
                                @else
                                    bg-gray-200 text-gray-400
                                @endif">
                                @if($stepNumber < $currentStep)
                                    <i class="fas fa-check"></i>
                                @else
                                    <i class="fas {{ $step['icon'] }}"></i>
                                @endif
                            </div>
                        @endif
                        
                        <span class="text-[10px] mt-1 text-gray-500 text-center max-w-[60px]">
                            {{ $step['name'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    /* Smooth transitions */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.8; }
    }
    
    /* Pulse animation for current step */
    [class*="ring-orange-100"] {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
