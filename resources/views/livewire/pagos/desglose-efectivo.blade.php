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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Columna Izquierda: Panel de Efectivo (Principal) --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Tarjeta de Billetes --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Billetes
                        </h2>
                        <span class="text-sm font-medium text-green-600 bg-green-100 px-3 py-1 rounded-full">
                            Subtotal: ${{ number_format(collect($desgloseBilletes)->map(fn($cant, $val) => $cant * $val)->sum(), 2) }}
                        </span>
                    </div>
                    <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-6">
                        @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                            <div class="relative group">
                                <div class="flex justify-center mb-2 h-16 items-center">
                                    <img src="{{ asset('img/billetes-monedas/billetes/' . $billete . 'pesos.png') }}" 
                                         alt="${{ $billete }}" 
                                         class="max-h-full max-w-full object-contain shadow-md rounded-sm transform transition-transform duration-200 group-hover:scale-105">
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-sm font-bold">#</span>
                                    </div>
                                    <input type="number" 
                                           min="0"
                                           wire:model.blur="desgloseBilletes.{{ $billete }}"
                                           class="block w-full pl-8 pr-3 py-3 border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-center text-lg font-semibold shadow-sm transition-colors"
                                           placeholder="0">
                                </div>
                                <div class="mt-1 text-center text-xs text-gray-400 font-medium">
                                    = ${{ number_format($billete * ($desgloseBilletes[$billete] ?? 0), 0) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tarjeta de Monedas --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-yellow-800 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Monedas
                        </h2>
                        <span class="text-sm font-medium text-yellow-600 bg-yellow-100 px-3 py-1 rounded-full">
                            Subtotal: ${{ number_format(collect($desgloseMonedas)->map(fn($cant, $val) => $cant * $val)->sum(), 2) }}
                        </span>
                    </div>
                    <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-6">
                        @foreach(['20', '10', '5', '2', '1', '0.5'] as $moneda)
                            @php
                                $imagen = match($moneda) {
                                    '1' => '1peso.png',
                                    '0.5' => '50centavos.png',
                                    default => $moneda . 'pesos.png'
                                };
                            @endphp
                            <div class="relative group">
                                <div class="flex justify-center mb-2 h-14 items-center">
                                    <img src="{{ asset('img/billetes-monedas/monedas/' . $imagen) }}" 
                                         alt="${{ $moneda }}" 
                                         class="max-h-full max-w-full object-contain  rounded-full transform transition-transform duration-200 group-hover:scale-110">
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-sm font-bold">#</span>
                                    </div>
                                    <input type="number" 
                                           min="0"
                                           wire:model.blur="desgloseMonedas.{{ $moneda }}"
                                           class="block w-full pl-8 pr-3 py-3 border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-center text-lg font-semibold shadow-sm transition-colors"
                                           placeholder="0">
                                </div>
                                <div class="mt-1 text-center text-xs text-gray-400 font-medium">
                                    = ${{ number_format($moneda * ($desgloseMonedas[$moneda] ?? 0), 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Notas --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label for="notas" class="block text-sm font-medium text-gray-700 mb-2">Notas Adicionales</label>
                    <textarea id="notas" wire:model="notas" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Cualquier observación sobre el pago..."></textarea>
                </div>

            </div>

            {{-- Columna Derecha: Resumen y Confirmación --}}
            <div class="lg:col-span-1 space-y-6">
                
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
                        {{--     @if($diferencia > 0 && !empty($this->desgloseCambio))
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
            
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg overflow-hidden z-10">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Confirmar Pago
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Por favor verifica los montos antes de finalizar.
                                </p>
                                
                                <div class="mt-4 bg-gray-50 p-3 rounded-md">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Total a Cobrar:</span>
                                        <span class="font-bold">${{ number_format($totalSeleccionado, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Efectivo Recibido:</span>
                                        <span class="font-bold text-blue-600">${{ number_format($totalEfectivo, 2) }}</span>
                                    </div>
                                    <div class="flex justify-between text-lg mt-2 pt-2 border-t border-gray-200">
                                        <span class="font-bold text-gray-700">Cambio a Entregar:</span>
                                        <span class="font-bold text-green-600">${{ number_format($diferencia, 2) }}</span>
                                    </div>
                                </div>

                                @if($diferencia > 0 && !empty($this->desgloseCambio))
                                    <div class="mt-4">
                                        <h4 class="text-sm font-bold text-gray-700 mb-2">Sugerencia de entrega:</h4>
                                        <div class="grid grid-cols-3 gap-3">
                                            @foreach($this->desgloseCambio as $denominacion => $cantidad)
                                                @php
                                                    $valor = (float)$denominacion;
                                                    $esBillete = $valor >= 20;
                                                    $path = $esBillete ? 'billetes/' : 'monedas/';
                                                    
                                                    // Normalizar nombre de archivo
                                                    if ($valor == 0.5) {
                                                        $filename = '50centavos';
                                                    } elseif ($valor == 1) {
                                                        $filename = '1peso';
                                                    } else {
                                                        $filename = $denominacion . 'pesos';
                                                    }
                                                @endphp
                                                <div wire:key="cambio-{{ $denominacion }}" class="flex flex-col items-center p-2 border rounded bg-gray-50">
                                                    <div class="h-12 flex items-center justify-center mb-1">
                                                        <img src="{{ asset('img/billetes-monedas/' . $path . $filename . '.png') }}" 
                                                             alt="${{ $denominacion }}" 
                                                             class="max-h-full max-w-full object-contain"
                                                             onerror="this.style.display='none'">
                                                    </div>
                                                    <span class="text-lg font-bold text-gray-800">{{ $cantidad }}x</span>
                                                    <span class="text-xs text-gray-500">${{ $denominacion }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            wire:click="finalizarRegistro" 
                            wire:loading.attr="disabled"
                            wire:target="finalizarRegistro"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="finalizarRegistro">Finalizar y Guardar</span>
                        <span wire:loading wire:target="finalizarRegistro">Guardando...</span>
                    </button>
                    <button type="button" wire:click="$set('showModalCambio', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
