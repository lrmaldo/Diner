<div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    @if(!$modo)
        <div class="flex items-center justify-center min-h-[50vh]">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 w-full max-w-4xl">
                {{-- Botón Pagos --}}
                <button wire:click="seleccionarModo('pagos')" class="h-48 bg-red-600 hover:bg-red-700 text-white text-4xl font-bold rounded shadow-lg transform transition hover:scale-105 flex items-center justify-center">
                    Pagos
                </button>

                {{-- Botón Multas --}}
                <button wire:click="seleccionarModo('multas')" class="h-48 bg-red-600 hover:bg-red-700 text-white text-4xl font-bold rounded shadow-lg transform transition hover:scale-105 flex items-center justify-center">
                    Multas
                </button>
            </div>
        </div>
    @elseif($modo === 'multas')
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 animate-fade-in-up">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Devolución de Garantía / Multas</h1>
                <button wire:click="$set('modo', null)" class="text-gray-600 hover:text-red-600 font-medium">
                    &larr; Volver
                </button>
            </div>
            
            {{-- Buscador --}}
        <div class="mb-8 flex flex-col md:flex-row gap-4 items-end">
             <div class="w-full md:w-1/3">
                 <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Grupo (ID Préstamo)</label>
                 <div class="relative rounded-md shadow-sm">
                     <input type="text" 
                            wire:model.live.debounce.300ms="search" 
                            wire:keydown.enter="buscarPrestamo"
                            id="search" 
                            class="focus:ring-red-500 focus:border-red-500 block w-full pl-4 pr-12 text-lg border-gray-300 rounded-md h-12" 
                            placeholder="Ingrese ID..."
                            autofocus>
                 </div>
             </div>
             <div class="w-full md:w-auto">
                 <button wire:click="buscarPrestamo" class="w-full md:w-auto bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-md transition duration-150 ease-in-out h-12 flex items-center justify-center">
                     Buscar
                 </button>
             </div>
        </div>

        {{-- Loading --}}
        <div wire:loading wire:target="search, buscarPrestamo" class="mb-4 text-center w-full">
            <span class="text-red-500 font-semibold">Cargando...</span>
        </div>

        {{-- Mensaje No Encontrado --}}
        @if($notFound)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <p class="text-sm text-red-700">No se encontró ningún préstamo o grupo con el ID <strong>{{ $search }}</strong>.</p>
            </div>
        @endif

        {{-- Tabla de Resultados --}}
        @if($prestamo)
            <div class="animate-fade-in-up">
                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-gray-500 text-sm block">Grupo / Cliente</span>
                            <span class="text-lg font-bold text-gray-800">
                                {{ $prestamo->grupo ? $prestamo->grupo->nombre : ($prestamo->cliente->nombre_completo ?? 'N/A') }}
                            </span>
                        </div>
                        <div class="text-right">
                             <span class="text-gray-500 text-sm block">ID Préstamo</span>
                             <span class="text-lg font-bold text-gray-800">#{{ $prestamo->id }}</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-red-600 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Nombre</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Garantía</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Devuelto</th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Saldo</th>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider w-10">
                                    <div class="flex flex-col items-center">
                                        <span class="mb-1">Sel.</span>
                                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-white text-red-600 focus:ring-red-500 h-4 w-4">
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider bg-red-700">Devolver</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $clientes = $prestamo->producto === 'grupal' ? $prestamo->clientes : ($prestamo->clientes->isNotEmpty() ? $prestamo->clientes : collect([$prestamo->cliente]));
                            @endphp

                            @foreach($clientes as $cliente)
                                @if(!$cliente) @continue @endif
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $cliente->nombre_completo }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900">
                                        ${{ number_format($garantias[$cliente->id] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900">
                                        ${{ number_format($devueltos[$cliente->id] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-900 font-bold">
                                        ${{ number_format($saldos[$cliente->id] ?? 0, 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center">
                                        <input type="checkbox" wire:model.live="selectedClients.{{ $cliente->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500 h-5 w-5">
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right bg-blue-50">
                                        <input type="number" 
                                               wire:model.live.debounce.500ms="montosDevolver.{{ $cliente->id }}"
                                               class="w-32 text-right border-blue-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-blue-700 font-bold"
                                               min="0"
                                               step="0.01">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-right text-xl font-bold text-gray-900">Total:</td>
                                <td class="px-4 py-4 text-right text-xl font-bold text-red-600 bg-red-50 border-t border-red-200">
                                    ${{ number_format($totalDevolverInput, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Acciones --}}
                <div class="mt-8 flex justify-center gap-6">
                    <button wire:click="procesarDevolucion" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-12 rounded shadow-lg transform transition hover:scale-105">
                        Cobrar/Devolver
                    </button>
                    <button wire:click="$set('prestamo', null)" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-12 rounded shadow-lg transform transition hover:scale-105">
                        Cancelar
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
</div>
