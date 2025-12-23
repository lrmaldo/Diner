<div class="min-h-screen bg-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Entrega de Créditos</h1>
            <p class="mt-1 text-sm text-gray-500">
                Desembolso de efectivo para préstamos aprobados.
            </p>
        </div>

        {{-- Buscador --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="max-w-xl">
                <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-2">Buscar Préstamo (ID)</label>
                <div class="flex gap-4">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="number" 
                               wire:model="busqueda" 
                               wire:keydown.enter="buscarPrestamo"
                               class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                               placeholder="Ingrese ID del préstamo...">
                    </div>
                    <button wire:click="buscarPrestamo" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cargar
                    </button>
                </div>
            </div>
        </div>

        @if($prestamo && $mostrarDesglose)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Columna Izquierda: Desglose de Billetes (Salida) --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Info del Préstamo --}}
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-md shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Detalles del Préstamo #{{ $prestamo->id }}</h3>
                                <div class="mt-2 text-sm text-blue-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <p><span class="font-bold">Cliente/Grupo:</span> {{ $prestamo->producto === 'grupal' ? ($prestamo->grupo->nombre ?? 'Grupo') : ($prestamo->cliente->nombre_completo ?? 'Cliente') }}</p>
                                    
                                    @if($prestamo->producto === 'grupal')
                                        <p><span class="font-bold">Representante:</span> {{ $prestamo->representante->nombre_completo ?? 'N/A' }}</p>
                                    @endif

                                    <p><span class="font-bold">Ejecutivo:</span> {{ $prestamo->asesor->name ?? 'No asignado' }}</p>

                                    <p><span class="font-bold">Monto Autorizado:</span> ${{ number_format($prestamo->monto_total, 2) }}</p>
                                    <p><span class="font-bold">Plazo:</span> {{ $prestamo->plazo }} {{ $prestamo->periodicidad }}</p>
                                    <p><span class="font-bold">Fecha Solicitud:</span> {{ $prestamo->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Billetes --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-green-50 px-6 py-4 border-b border-green-100 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-green-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Billetes a Entregar
                            </h2>
                            <span class="text-sm font-medium text-green-600 bg-green-100 px-3 py-1 rounded-full">
                                Subtotal: ${{ number_format(collect($desgloseBilletes)->map(fn($cant, $val) => (float)$cant * $val)->sum(), 2) }}
                            </span>
                        </div>
                        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-6">
                            @foreach(['1000', '500', '200', '100', '50', '20'] as $billete)
                                <div class="relative group">
                                    <div class="flex justify-center mb-2 h-16 items-center">
                                        <img src="{{ asset('img/billetes-monedas/billetes/' . $billete . 'pesos.png') }}" 
                                             class="max-h-full max-w-full object-contain shadow-md rounded-sm">
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-400 text-sm font-bold">#</span>
                                        </div>
                                        <input type="number" 
                                               min="0"
                                               wire:model.live.debounce.500ms="desgloseBilletes.{{ $billete }}"
                                               class="block w-full pl-8 pr-3 py-3 border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 text-center text-lg font-semibold shadow-sm transition-colors"
                                               placeholder="0">
                                    </div>
                                    <div class="mt-1 text-center text-xs text-gray-400 font-medium">
                                        = ${{ number_format($billete * (float)($desgloseBilletes[$billete] ?? 0), 0) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Monedas --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-yellow-50 px-6 py-4 border-b border-yellow-100 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-yellow-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Monedas a Entregar
                            </h2>
                            <span class="text-sm font-medium text-yellow-600 bg-yellow-100 px-3 py-1 rounded-full">
                                Subtotal: ${{ number_format(collect($desgloseMonedas)->map(fn($cant, $val) => (float)$cant * $val)->sum(), 2) }}
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
                                             class="max-h-full max-w-full object-contain rounded-full">
                                    </div>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-400 text-sm font-bold">#</span>
                                        </div>
                                        <input type="number" 
                                               min="0"
                                               wire:model.live.debounce.500ms="desgloseMonedas.{{ $moneda }}"
                                               class="block w-full pl-8 pr-3 py-3 border-gray-300 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-center text-lg font-semibold shadow-sm transition-colors"
                                               placeholder="0">
                                    </div>
                                    <div class="mt-1 text-center text-xs text-gray-400 font-medium">
                                        = ${{ number_format($moneda * (float)($desgloseMonedas[$moneda] ?? 0), 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <label for="notas" class="block text-sm font-medium text-gray-700 mb-2">Notas de Entrega</label>
                        <textarea id="notas" wire:model="notas" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Observaciones sobre la entrega..."></textarea>
                    </div>

                </div>

                {{-- Columna Derecha: Resumen --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden sticky top-6">
                        <div class="bg-indigo-900 px-6 py-4">
                            <h3 class="text-lg font-bold text-white">Resumen de Entrega</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            
                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Monto a Entregar</span>
                                <span class="text-2xl font-bold text-gray-900">${{ number_format($totalEntregar, 2) }}</span>
                            </div>

                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <span class="text-gray-600">Efectivo Seleccionado</span>
                                <span class="text-2xl font-bold text-blue-600">${{ number_format($totalSeleccionado, 2) }}</span>
                            </div>

                            <div class="rounded-lg p-4 {{ abs($diferencia) < 0.01 ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100' }}">
                                <div class="text-center">
                                    <span class="block text-sm font-medium {{ abs($diferencia) < 0.01 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ abs($diferencia) < 0.01 ? 'MONTO EXACTO' : ($diferencia > 0 ? 'SOBRA DINERO' : 'FALTA DINERO') }}
                                    </span>
                                    @if(abs($diferencia) >= 0.01)
                                        <span class="block text-xl font-extrabold mt-1 text-red-700">
                                            Diferencia: ${{ number_format(abs($diferencia), 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <button wire:click="confirmarEntrega" 
                                    wire:loading.attr="disabled"
                                    wire:target="confirmarEntrega"
                                    @if(abs($diferencia) >= 0.01) disabled @endif
                                    class="w-full flex justify-center py-4 px-4 border border-transparent rounded-lg shadow-sm text-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <span wire:loading.remove wire:target="confirmarEntrega">Confirmar Entrega</span>
                                <span wire:loading wire:target="confirmarEntrega">Procesando...</span>
                            </button>

                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
