<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header con navegación --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Desglose de Efectivo</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Préstamo #{{ $prestamo->id }} - {{ $prestamo->producto === 'grupal' ? ($grupo->nombre ?? 'Grupo') : 'Individual' }}
                </p>
            </div>
            <a href="{{ route('pagos.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Volver a Búsqueda
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            {{-- Columna Izquierda: Panel de Efectivo (Principal) --}}
            <div class="lg:col-span-8 space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tarjeta de Billetes --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50 px-4 py-2 border-b border-green-100 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-green-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Billetes
                            </h2>
                            <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                                ${{ number_format(collect($desgloseBilletes)->map(fn($cant, $val) => (float)$cant * $val)->sum(), 2) }}
                            </span>
                        </div>
                        <div class="p-2 space-y-1">
                            @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                                <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100 transition-colors">
                                    <div class="flex items-center gap-2 w-1/3">
                                        <img src="{{ asset('img/billetes-monedas/billetes/' . $billete . 'pesos.png') }}" 
                                             alt="${{ $billete }}" 
                                             class="h-8 w-auto object-contain shadow-sm rounded-sm">
                                        <span class="text-xs font-bold text-gray-500 hidden sm:inline">${{ $billete }}</span>
                                    </div>
                                    <div class="w-1/3 px-2">
                                        <input type="number" 
                                               min="0"
                                               wire:model.live.debounce.500ms="desgloseBilletes.{{ $billete }}"
                                               class="block w-full text-center border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 text-sm font-semibold py-1"
                                               placeholder="0">
                                    </div>
                                    <div class="w-1/3 text-right text-xs text-gray-600 font-medium">
                                        ${{ number_format($billete * (float)($desgloseBilletes[$billete] ?? 0), 0) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Tarjeta de Monedas --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-yellow-50 px-4 py-2 border-b border-yellow-100 flex items-center justify-between">
                            <h2 class="text-base font-semibold text-yellow-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Monedas
                            </h2>
                            <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full">
                                ${{ number_format(collect($desgloseMonedas)->map(fn($cant, $val) => (float)$cant * $val)->sum(), 2) }}
                            </span>
                        </div>
                        <div class="p-2 space-y-1">
                            @foreach(['20', '10', '5', '2', '1', '0_5'] as $monedaKey)
                                @php
                                    $valor = $monedaKey === '0_5' ? 0.5 : (float)$monedaKey;
                                    $imagen = match($monedaKey) {
                                        '1' => '1peso.png',
                                        '0_5' => '50centavos.png',
                                        default => $monedaKey . 'pesos.png'
                                    };
                                @endphp
                                <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100 transition-colors">
                                    <div class="flex items-center gap-2 w-1/3">
                                        <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}" 
                                             alt="${{ $valor }}" 
                                             class="h-8 w-8 object-contain rounded-full">
                                        <span class="text-xs font-bold text-gray-500 hidden sm:inline">${{ $valor }}</span>
                                    </div>
                                    <div class="w-1/3 px-2">
                                        <input type="number" 
                                               min="0"
                                               wire:model.live.debounce.500ms="desgloseMonedas.{{ $monedaKey }}"
                                               class="block w-full text-center border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500 text-sm font-semibold py-1"
                                               placeholder="0">
                                    </div>
                                    <div class="w-1/3 text-right text-xs text-gray-600 font-medium">
                                        ${{ number_format($valor * (float)($desgloseMonedas[$monedaKey] ?? 0), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Notas --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <label for="notas" class="block text-xs font-medium text-gray-700 mb-1">Notas Adicionales</label>
                    <textarea id="notas" wire:model="notas" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Cualquier observación sobre el pago..."></textarea>
                </div>

            </div>

            {{-- Columna Derecha: Resumen y Confirmación --}}
            <div class="lg:col-span-4 space-y-4">
                
                {{-- Tarjeta de Resumen Financiero --}}
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden sticky top-6">
                    <div class="bg-gray-900 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">Resumen de Cobro</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        
                        {{-- Total a Cobrar --}}
                        <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <span class="text-gray-600">Total a Cobrar</span>
                            <span class="text-2xl font-bold text-gray-900">${{ number_format($totalSeleccionado, 2) }}</span>
                        </div>

                        {{-- Efectivo Recibido --}}
                        <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                            <span class="text-gray-600">Efectivo Recibido</span>
                            <span class="text-2xl font-bold text-blue-600">${{ number_format($totalEfectivo, 2) }}</span>
                        </div>

                        {{-- Diferencia (Cambio o Faltante) --}}
                        <div class="rounded-lg p-4 {{ $diferencia < 0 ? 'bg-red-50 border border-red-100' : ($diferencia > 0 ? 'bg-green-50 border border-green-100' : 'bg-gray-50 border border-gray-100') }}">
                            <div class="text-center">
                                <span class="block text-sm font-medium {{ $diferencia < 0 ? 'text-red-600' : ($diferencia > 0 ? 'text-green-600' : 'text-gray-500') }}">
                                    {{ $diferencia < 0 ? 'FALTAN' : ($diferencia > 0 ? 'CAMBIO A ENTREGAR' : 'CUENTA SALDADA') }}
                                </span>
                                <span class="block text-3xl font-extrabold mt-1 {{ $diferencia < 0 ? 'text-red-700' : ($diferencia > 0 ? 'text-green-700' : 'text-gray-700') }}">
                                    ${{ number_format(abs($diferencia), 2) }}
                                </span>
                            </div>

                            {{-- Desglose de Cambio Sugerido --}}
                         {{--    @if($diferencia > 0 && !empty($this->desgloseCambio))
                                <div class="mt-3 pt-3 border-t border-green-200">
                                    <p class="text-xs font-bold text-green-800 uppercase mb-2 text-center">Sugerencia de entrega:</p>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        @foreach($this->desgloseCambio as $denominacion => $cantidad)
                                            <div class="flex justify-between items-center bg-white px-2 py-1 rounded border border-green-100">
                                                <span class="font-medium text-gray-600">{{ $cantidad }}x</span>
                                                <span class="font-bold text-green-700">${{ $denominacion }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif --}}
                        </div>

                        {{-- Botón de Acción --}}
                        <button wire:click="validarRegistro" 
                                wire:loading.attr="disabled"
                                wire:target="validarRegistro"
                                class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="validarRegistro">Confirmar y Registrar</span>
                            <span wire:loading wire:target="validarRegistro">Procesando...</span>
                        </button>

                    </div>

                    {{-- Lista de Clientes (Colapsable o Resumida) --}}
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Detalle por Cliente</h4>
                        <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($clientes as $cliente)
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" 
                                               wire:model.live="clientesSeleccionados.{{ $cliente->id }}" 
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <span class="text-gray-700 truncate max-w-[120px]" title="{{ $cliente->nombre_completo }}">
                                            {{ $cliente->nombres }} {{ $cliente->apellido_paterno }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-gray-500 text-xs">$</span>
                                        <input type="number" 
                                               wire:model.live.debounce.500ms="montosPorCliente.{{ $cliente->id }}" 
                                               class="w-16 text-right text-xs border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 p-1"
                                               min="0">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal de Confirmación y Cambio --}}
    @if($showModalCambio)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showModalCambio', false)"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl overflow-hidden z-10 max-h-[90vh] flex flex-col">
                <div class="bg-white px-4 pt-4 pb-2 sm:p-6 sm:pb-2 flex-shrink-0">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center gap-2" id="modal-title">
                            <div class="flex-shrink-0 flex items-center justify-center h-8 w-8 rounded-full bg-green-100">
                                <svg class="h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            Confirmar Pago y Cambio
                        </h3>
                        <button wire:click="$set('showModalCambio', false)" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-md grid grid-cols-3 gap-4 text-center">
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Total a Cobrar</span>
                            <span class="block text-lg font-bold text-gray-900">${{ number_format($totalSeleccionado, 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Efectivo Recibido</span>
                            <span class="block text-lg font-bold text-blue-600">${{ number_format($totalEfectivo, 2) }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-500 uppercase">Cambio a Entregar</span>
                            <span class="block text-lg font-bold text-green-600">${{ number_format($diferencia, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-2">
                    @if($diferencia > 0)
                        <div class="border-t border-gray-200 pt-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-sm font-bold text-gray-700">Desglose de Cambio (Manual)</h4>
                                <div class="text-sm">
                                    @php $faltante = round($diferencia - $totalCambioManual, 2); @endphp
                                    @if($faltante > 0)
                                        <span class="text-red-600 font-bold bg-red-50 px-2 py-1 rounded">Faltan: ${{ number_format($faltante, 2) }}</span>
                                    @elseif($faltante < 0)
                                        <span class="text-orange-600 font-bold bg-orange-50 px-2 py-1 rounded">Sobran: ${{ number_format(abs($faltante), 2) }}</span>
                                    @else
                                        <span class="text-green-600 font-bold bg-green-50 px-2 py-1 rounded flex items-center gap-1">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Correcto
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Billetes --}}
                                <div>
                                    <h5 class="text-xs font-semibold text-gray-500 mb-2 border-b pb-1">Billetes</h5>
                                    <div class="space-y-1">
                                        @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                                            <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100">
                                                <div class="flex items-center gap-2 w-1/3">
                                                    <img src="{{ asset('img/billetes-monedas/billetes/' . $billete . 'pesos.png') }}" 
                                                         class="h-6 w-auto object-contain">
                                                    <span class="text-xs font-bold text-gray-500">${{ $billete }}</span>
                                                </div>
                                                <div class="w-1/3 px-2">
                                                    <input type="number" 
                                                           min="0" 
                                                           wire:model.live.debounce.500ms="desgloseCambioBilletes.{{ $billete }}" 
                                                           class="block w-full text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold py-1 h-8"
                                                           placeholder="0">
                                                </div>
                                                <div class="w-1/3 text-right text-xs text-gray-400">
                                                    ${{ number_format($billete * (float)($desgloseCambioBilletes[$billete] ?? 0), 0) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Monedas --}}
                                <div>
                                    <h5 class="text-xs font-semibold text-gray-500 mb-2 border-b pb-1">Monedas</h5>
                                    <div class="space-y-1">
                                        @foreach(['20', '10', '5', '2', '1', '0_5'] as $monedaKey)
                                             @php
                                                $valor = $monedaKey === '0_5' ? 0.5 : (float)$monedaKey;
                                                $imagen = match($monedaKey) {
                                                    '1' => '1peso.png',
                                                    '0_5' => '50centavos.png',
                                                    default => $monedaKey . 'pesos.png'
                                                };
                                            @endphp
                                            <div class="flex items-center justify-between p-1 hover:bg-gray-50 rounded border border-transparent hover:border-gray-100">
                                                <div class="flex items-center gap-2 w-1/3">
                                                    <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}" 
                                                         class="h-6 w-6 object-contain rounded-full">
                                                    <span class="text-xs font-bold text-gray-500">${{ $valor }}</span>
                                                </div>
                                                <div class="w-1/3 px-2">
                                                    <input type="number" 
                                                           min="0" 
                                                           wire:model.live.debounce.500ms="desgloseCambioMonedas.{{ $monedaKey }}" 
                                                           class="block w-full text-center border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-sm font-semibold py-1 h-8"
                                                           placeholder="0">
                                                </div>
                                                <div class="w-1/3 text-right text-xs text-gray-400">
                                                    ${{ number_format($valor * (float)($desgloseCambioMonedas[$monedaKey] ?? 0), 2) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2 flex-shrink-0">
                    <button type="button" 
                            wire:click="finalizarRegistro" 
                            wire:loading.attr="disabled"
                            wire:target="finalizarRegistro"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="finalizarRegistro">Finalizar y Guardar</span>
                        <span wire:loading wire:target="finalizarRegistro">Guardando...</span>
                    </button>
                    <button type="button" wire:click="$set('showModalCambio', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
