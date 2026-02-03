<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    
    @if(!$modo)
        {{-- Menú de Retiro: Pagos (Devoluciones) vs Multas --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12 flex justify-center items-center gap-16 min-h-[400px]">
            <button wire:click="seleccionarModo('pagos')" class="bg-red-600 hover:bg-red-700 text-white font-bold text-3xl py-12 px-16 rounded shadow-lg transform transition hover:scale-105">
                Pagos
            </button>
            
            <button wire:click="seleccionarModo('multas')" class="bg-red-600 hover:bg-red-700 text-white font-bold text-3xl py-12 px-16 rounded shadow-lg transform transition hover:scale-105">
                Multas
            </button>
        </div>
    @else
        {{-- Vista de Operación (Pagos/Multas) --}}
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ $modo === 'pagos' ? 'Devolución de Garantías' : 'Retiro de Multas' }}</h1>
                <button wire:click="seleccionarModo(null)" class="text-gray-500 hover:text-gray-700 font-bold flex items-center">
                    &larr; Volver al menú
                </button>
            </div>
        
        {{-- Buscador --}}
        <div class="mb-8 flex flex-col md:flex-row gap-4 items-end">
             <div class="w-full md:w-1/3">
                 <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Grupo (ID Préstamo)</label>
                 <div class="flex gap-0">
                     <input type="text" 
                            wire:model.live.debounce.300ms="search" 
                            wire:keydown.enter="buscarPrestamo"
                            id="search" 
                            class="focus:ring-red-500 focus:border-red-500 block w-full pl-4 pr-12 text-lg border-gray-300 rounded-l-md h-12" 
                            placeholder="Ingrese ID..."
                            autofocus>
                    <button wire:click="buscarPrestamo" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 rounded-r-md transition duration-150 ease-in-out h-12 flex items-center justify-center">
                         Buscar
                     </button>
                 </div>
             </div>
        </div>

        {{-- Loading --}}
        <div wire:loading wire:target="search, buscarPrestamo" class="mb-4 text-center w-full">
            <span class="text-red-500 font-semibold">Buscando...</span>
        </div>

        {{-- Mensaje de Error (No liquidado, etc) --}}
        @if($errorMessage)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 animate-shake">
                <p class="text-lg font-bold text-red-700">{{ $errorMessage }}</p>
            </div>
        @elseif($notFound)
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                <p class="text-sm text-yellow-700">No se encontró ningún préstamo con el ID <strong>{{ $search }}</strong>.</p>
            </div>
        @endif

        @if($prestamo)
            @if($modo === 'multas')
                <div class="animate-fade-in-up mt-8">
                    {{-- Tabla de Multas --}}
                    <div class="flex justify-end mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model.live="selectAllMultas" class="form-checkbox h-5 w-5 text-red-600 rounded border-gray-300 focus:ring-red-500">
                            <span class="ml-2 text-gray-700">Seleccionar todo</span>
                        </label>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-red-600">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Penalizacion</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Recuperado</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Saldo</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Pagar</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($multasData as $row)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-r border-gray-200">
                                            {{ $row['nombre'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 border-r border-gray-200">
                                            ${{ number_format($row['penalizacion'], 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 border-r border-gray-200">
                                            ${{ number_format($row['recuperado'], 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 border-r border-gray-200">
                                            ${{ number_format($row['saldo'], 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <input type="checkbox" wire:model.live="multasSelected.{{ $row['id'] }}" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                <span class="text-blue-700 font-bold text-lg cursor-pointer" wire:click="$toggle('multasSelected.{{ $row['id'] }}')">
                                                    {{ number_format($row['saldo'], 0) }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totales y Acciones --}}
                    <div class="mt-8 flex justify-end items-center gap-8 px-4">
                        <div class="text-3xl font-bold text-red-600">
                            Total <span class="ml-4">{{ number_format($totalPagarMultas, 0) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-8 mt-12 pb-8">
                        <button wire:click="iniciarCobroMultas" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-12 rounded text-xl shadow transform transition hover:scale-105">
                            Cobrar
                        </button>
                        <button wire:click="$set('prestamo', null)" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-12 rounded text-xl shadow transform transition hover:scale-105">
                            Cancelar
                        </button>
                    </div>
                </div>
            @else
            <div class="animate-fade-in-up mt-8 max-w-2xl mx-auto border border-gray-200 rounded-lg p-8 shadow-sm">
                
                <div class="grid grid-cols-1 gap-6 mb-8">
                    {{-- Grupo / Cliente --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Grupo</label>
                        <div class="w-full border border-gray-300 rounded-md px-4 py-3 bg-gray-50 text-gray-900 text-lg">
                            {{ $prestamo->grupo ? $prestamo->grupo->nombre : ($prestamo->cliente->nombre_completo ?? 'N/A') }}
                        </div>
                    </div>

                    {{-- Representante --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Representante</label>
                        <div class="w-full border border-gray-300 rounded-md px-4 py-3 bg-gray-50 text-gray-900 text-lg">
                            {{ $representanteName }}
                        </div>
                    </div>

                    {{-- Ejecutivo --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Ejecutivo</label>
                        <div class="w-full border border-gray-300 rounded-md px-4 py-3 bg-gray-50 text-gray-900 text-lg">
                            {{ $ejecutivoName }}
                        </div>
                    </div>

                    {{-- Monto Garantía --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Monto de Garantía</label>
                        <div class="w-full border border-gray-300 rounded-md px-4 py-3 bg-white text-gray-900 text-xl font-bold">
                            ${{ number_format($montoGarantiaTotal, 2) }}
                        </div>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex justify-between gap-4 mt-8">
                    @if(!$errorMessage)
                        <button wire:click="iniciarDevolucion" class="w-1/2 bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded text-lg shadow transition transform hover:scale-105">
                            Devolver
                        </button>
                    @else
                        <button disabled class="w-1/2 bg-gray-400 cursor-not-allowed text-white font-bold py-3 px-4 rounded text-lg shadow">
                            Devolver
                        </button>
                    @endif
                    <button wire:click="$set('prestamo', null)" class="w-1/2 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-4 rounded text-lg shadow transition transform hover:scale-105">
                        Cancelar
                    </button>
                </div>
            </div>
            @endif
        @endif
    </div>
    @endif
</div>
