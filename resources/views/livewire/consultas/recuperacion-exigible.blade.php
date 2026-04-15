<div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 bg-blue-600 text-white rounded-t-lg">
            <h3 class="text-lg font-medium text-center">PERIODO DE LA CONSULTA</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                <div class="flex items-center justify-between gap-4">
                    <label class="font-medium text-gray-700 whitespace-nowrap min-w-[80px]">Desde el:</label>
                    <input type="date" wire:model="fechaDesde" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm pt-2 pb-2 pl-2">
                </div>
                
                <div class="flex items-center justify-between gap-4">
                    <label class="font-medium text-gray-700 whitespace-nowrap min-w-[80px]">Hasta el:</label>
                    <input type="date" wire:model="fechaHasta" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm pt-2 pb-2 pl-2">
                </div>
            </div>
            
            <div class="mt-6 flex justify-center">
                <button wire:click="generateReport" type="button" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                    <span wire:loading.remove wire:target="generateReport">Generar</span>
                    <span wire:loading wire:target="generateReport">Generando...</span>
                </button>
            </div>
        </div>
    </div>

    @if(isset($resultados))
    <div class="mt-8">
        <div class="mb-4 inline-block bg-blue-600 text-white px-4 py-1 rounded text-sm relative">
            <span class="font-medium">Periodo de consulta del {{ \Carbon\Carbon::parse($fechaDesde)->translatedFormat('d \d\e F \d\e Y') }} al {{ \Carbon\Carbon::parse($fechaHasta)->translatedFormat('d \d\e F \d\e Y') }}</span>
        </div>

        <div class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-blue-600">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6 border border-gray-300 border-opacity-70">ASESOR</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-white border border-gray-300 border-opacity-70">EXIGIBLE</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-white border border-gray-300 border-opacity-70">RECUPERADO</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-white border border-gray-300 border-opacity-70">PENDIENTE</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-white border border-gray-300 border-opacity-70">EFEC. %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($resultados as $fila)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium border border-gray-200">
                            <!-- Link azul como pidio Marcos -->
                            <a href="{{ route('consultas.recuperacion.asesor', ['asesor_id' => $fila['id'], 'desde' => $fechaDesde, 'hasta' => $fechaHasta]) }}" class="text-blue-600 hover:text-blue-800 hover:underline">
                                {{ $fila['nombre'] }}
                            </a>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ number_format($fila['exigible'], 0) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ number_format($fila['recuperado'], 0) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ number_format($fila['pendiente'], 0) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 border border-gray-200 text-center">{{ number_format($fila['eficiencia'], 2) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-sm text-gray-500">No se encontraron resultados para este periodo.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
