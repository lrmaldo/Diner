<div class="p-4 max-w-full mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold">Préstamos En Proceso</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('prestamos.create') }}" class="btn-primary text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Préstamo
            </a>
            <a href="{{ route('prestamos.index') }}" class="btn-outline text-center">Ver todos</a>
            <div class="text-sm text-gray-600">
                Total: {{ $prestamos->total() }} préstamo{{ $prestamos->total() !== 1 ? 's' : '' }}
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Búsqueda general -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text" id="search" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Folio, cliente o representante">
                </div>
            </div>

            <!-- Filtro por producto -->
            <div>
                <label for="producto" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select wire:model.live="producto" id="producto" class="input-project">
                    <option value="">Todos</option>
                    <option value="individual">Individual</option>
                    <option value="grupal">Grupal</option>
                </select>
            </div>

            <!-- Registros por página -->
            <div>
                <label for="perPage" class="block text-sm font-medium text-gray-700 mb-1">Mostrar</label>
                <select wire:model.live="perPage" id="perPage" class="input-project">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <!-- Filtros de fecha -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Fecha desde -->
            <div>
                <label for="fechaDesde" class="block text-sm font-medium text-gray-700 mb-1">Fecha creación desde</label>
                <input wire:model.live="fechaDesde" type="date" id="fechaDesde" class="input-project">
            </div>

            <!-- Fecha hasta -->
            <div>
                <label for="fechaHasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha creación hasta</label>
                <input wire:model.live="fechaHasta" type="date" id="fechaHasta" class="input-project">
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
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">ID</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Fecha creación</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Tipo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Integrantes</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Monto (Estimado)</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Cliente / Representante</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Última actualización</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamos as $p)
                            <tr class="border-t hover:bg-gray-50 transition-colors">
                                <td class="px-3 py-3 font-medium text-sm">{{ $p->id }}</td>
                                <td class="px-3 py-3 text-sm">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-3 py-3 text-sm">
                                    <span class="capitalize">{{ $p->producto ?? 'N/A' }}</span>
                                </td>
                                <td class="px-3 py-3 text-sm text-center">
                                    @if($p->producto === 'grupal')
                                        {{ $p->clientes->count() }}
                                    @else
                                        1
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    ${{ number_format($p->monto_total ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    @if($p->producto === 'grupal')
                                        {{ $p->representante->nombre_completo ?? 'Sin representante' }}
                                    @else
                                        {{ $p->cliente->nombre_completo ?? 'Sin cliente' }}
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm text-gray-500">
                                    {{ $p->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-3 py-3 text-sm text-right whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="eliminarPrestamo({{ $p->id }})" 
                                                wire:confirm="¿Estás seguro de eliminar este préstamo en proceso? Esta acción no se puede deshacer."
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" 
                                                title="Eliminar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Eliminar
                                        </button>
                                        <a href="{{ route('prestamos.edit', $p) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" title="Continuar edición">
                                            Continuar
                                            <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay préstamos en proceso</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron préstamos pendientes de enviar a comité.</p>
                <div class="mt-6">
                    <a href="{{ route('prestamos.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Crear nuevo préstamo
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
