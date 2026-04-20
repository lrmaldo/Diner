<div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 bg-white flex items-center justify-between">
            <a href="javascript:history.back()" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                <svg class="mr-1 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
                </svg>
                Atrás
            </a>
            <h3 class="text-xl font-bold text-center text-blue-600 flex-1 border border-blue-600 p-2 ml-4 mr-4">Recuperación</h3>
            <div class="w-16"></div>
        </div>

        <div class="p-4 border-b border-gray-200 text-center font-medium bg-white">
            <span class="text-gray-700">asesor:</span> <span class="text-gray-900 border border-blue-600 px-4 py-1">{{ $asesor->name }}</span>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                <div class="flex items-center justify-between gap-4">
                    <label class="font-medium text-gray-700 whitespace-nowrap min-w-[80px]">Desde el:</label>
                    <input type="date" wire:model="fechaDesde" class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 pt-2 pb-2 pl-2">
                </div>
                
                <div class="flex items-center justify-between gap-4">
                    <label class="font-medium text-gray-700 whitespace-nowrap min-w-[80px]">Hasta el:</label>
                    <input type="date" wire:model="fechaHasta" class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-blue-500 pt-2 pb-2 pl-2">
                </div>
            </div>
            
            <div class="mt-6 flex justify-center">
                <button wire:click="generateReport" type="button" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <span wire:loading.remove wire:target="generateReport">Generar</span>
                    <span wire:loading wire:target="generateReport">Generando...</span>
                </button>
            </div>
        </div>
    </div>

    @if(isset($resultados))
    <div class="mt-8 overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-blue-600">
                <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70">Grupo</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70">Representante</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-white border border-gray-300 border-opacity-70">vencimiento</th>
                    <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-white border border-gray-300 border-opacity-70">Pago</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70">Exigible</th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70">Recuperado</th>
                    <th wire:click="sortBy('pendiente')" scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70 cursor-pointer hover:bg-blue-700">
                        Pendiente
                        @if($sortColumn === 'pendiente')
                            <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                        @endif
                    </th>
                    <th wire:click="sortBy('eficiencia')" scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white border border-gray-300 border-opacity-70 cursor-pointer hover:bg-blue-700">
                        Efici %
                        @if($sortColumn === 'eficiencia')
                            <span>{!! $sortDirection === 'asc' ? '&#8593;' : '&#8595;' !!}</span>
                        @endif
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($resultados as $fila)
                <tr>
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 border border-gray-200">
                        <a href="{{ route('prestamos.print', ['prestamo' => $fila['prestamo_id'], 'type' => 'estado_cuenta']) }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                            {{ $fila['grupo'] }}
                        </a>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200">{{ $fila['representante'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ $fila['vencimiento'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ $fila['pago'] }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200">{{ number_format($fila['exigible'], 0) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200">{{ number_format($fila['recuperado'], 0) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200">{{ number_format($fila['pendiente'], 0) }}</td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200">{{ number_format($fila['eficiencia'], 0) }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-4 text-center text-sm text-gray-500">No se encontraron cobros programados para este asesor en el periodo indicado.</td>
                </tr>
                @endforelse
            </tbody>
            @if(count($resultados) > 0)
            @php
                $resCollect = collect($resultados);
                $totalExigible = $resCollect->sum('exigible');
                $totalRecuperado = $resCollect->sum('recuperado');
                $totalPendiente = $resCollect->sum('pendiente');
                $eficienciaTotal = $totalExigible > 0 ? ($totalRecuperado / $totalExigible) * 100 : 100;
            @endphp
            <tfoot class="bg-blue-800 text-white font-bold">
                <tr>
                    <td colspan="4" class="py-3 pl-4 pr-3 text-left text-sm border border-gray-300 border-opacity-70">TOTAL</td>
                    <td class="px-3 py-3 text-left text-sm border border-gray-300 border-opacity-70">{{ number_format($totalExigible, 0) }}</td>
                    <td class="px-3 py-3 text-left text-sm border border-gray-300 border-opacity-70">{{ number_format($totalRecuperado, 0) }}</td>
                    <td class="px-3 py-3 text-left text-sm border border-gray-300 border-opacity-70">{{ number_format($totalPendiente, 0) }}</td>
                    <td class="px-3 py-3 text-left text-sm border border-gray-300 border-opacity-70">{{ number_format($eficienciaTotal, 2) }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    @endif
</div>
