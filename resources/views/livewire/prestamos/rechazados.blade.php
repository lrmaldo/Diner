<div class="p-4 max-w-full mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold">Préstamos Rechazados</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('prestamos.en-comite') }}" class="btn-outline text-center">Ver en comité</a>
            <a href="{{ route('prestamos.index') }}" class="btn-outline text-center">Ver todos</a>
            <div class="text-sm text-gray-600">
                Total: {{ $prestamos->total() }} préstamo{{ $prestamos->total() !== 1 ? 's' : '' }}
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        @if(auth()->check() && auth()->user()->hasRole('Asesor'))
            <div class="px-4 py-3 bg-yellow-50 border-l-4 border-yellow-300 rounded-b mb-4">
                <p class="text-sm text-yellow-800">Mostrando únicamente los préstamos que están asignados a usted como asesor.</p>
            </div>
        @endif
        @if($prestamos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Grupo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Fecha rechazo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Tipo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Monto</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Representante</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Motivo Rechazo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamos as $p)
                            <tr class="border-t hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-3 font-medium text-sm">{{ $p->id }}</td>
                                <td class="px-3 py-3 text-sm">{{ $p->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-sm">
                                    <span class="capitalize">{{ $p->producto ?? 'N/A' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    ${{ number_format($p->monto_total, 2) }}
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    @if($p->producto === 'grupal')
                                        {{ $p->representante->nombre_completo ?? 'N/A' }}
                                    @else
                                        {{ $p->cliente->nombre_completo ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-red-600 max-w-xs truncate" title="{{ $p->motivo_rechazo }}">
                                    {{ $p->motivo_rechazo ?? 'Sin motivo especificado' }}
                                </td>
                                <td class="px-3 py-3 text-sm text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('prestamos.show', $p) }}" class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        
                                        @if(auth()->user()->can('editar prestamos') || auth()->user()->hasRole('Asesor'))
                                        <a href="{{ route('prestamos.edit', $p) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <button wire:click="reenviarAComite({{ $p->id }})" 
                                                wire:confirm="¿Estás seguro de reenviar este préstamo a comité? Asegúrate de haber realizado las correcciones necesarias."
                                                class="text-green-600 hover:text-green-900" 
                                                title="Reenviar a Comité">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                            </svg>
                                        </button>

                                        <button wire:click="eliminarPrestamo({{ $p->id }})" 
                                                wire:confirm="¿Estás seguro de eliminar este préstamo? Esta acción no se puede deshacer."
                                                class="text-red-600 hover:text-red-900" 
                                                title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $prestamos->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay préstamos rechazados</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron préstamos rechazados con los filtros seleccionados.</p>
            </div>
        @endif
    </div>
</div>
