<div class="p-4 max-w-full mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold">Préstamos en Comité</h1>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('prestamos.autorizados') }}" class="btn-outline text-center">Ver autorizados</a>
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
                <label for="fechaDesde" class="block text-sm font-medium text-gray-700 mb-1">Fecha desde</label>
                <input wire:model.live="fechaDesde" type="date" id="fechaDesde" class="input-project">
            </div>

            <!-- Fecha hasta -->
            <div>
                <label for="fechaHasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha hasta</label>
                <input wire:model.live="fechaHasta" type="date" id="fechaHasta" class="input-project">
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        @if($prestamos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Folio</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Fecha de solicitud</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Tipo de Producto</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Integrantes</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Monto</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Representante</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Plazo</th>
                            {{-- Estado removido en esta vista (no necesario) --}}
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
                                <td class="px-3 py-3 font-medium text-sm">
                                    ${{ number_format($p->monto_total ?? 0, 2) }}
                                </td>
                                <td class="px-3 py-3 text-sm">
                                    @php
                                        $representante = $p->representante;
                                    @endphp
                                    @if($representante)
                                        {{ trim(($representante->nombres ?? '').' '.($representante->apellido_paterno ?? '')) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm">{{ $p->plazo }} meses</td>
                                {{-- Estado removido en esta vista --}}
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Ver detalles -->

                                        <!-- Evaluar (para tomar decisión) -->
                                        <a href="{{ route('prestamos.show', $p->id) }}"
                                           class="inline-flex items-center px-3 py-1 border border-blue-300 shadow-sm text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Evaluar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t">
                {{ $prestamos->links() }}
            </div>
        @else
            <div class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay préstamos en comité</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(!empty($this->search) || !empty($this->producto) || !empty($this->fechaDesde) || !empty($this->fechaHasta))
                        No se encontraron préstamos en comité que coincidan con los filtros aplicados.
                    @else
                        No hay préstamos esperando la decisión del comité en este momento.
                    @endif
                </p>
            </div>
        @endif
    </div>

    <!-- Información adicional para administradores -->
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Vista de Comité - Solo Administradores y asesor asignado</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Esta vista muestra únicamente los préstamos que están pendientes de decisión del comité. Como administrador, puedes evaluar cada préstamo y tomar la decisión de autorizarlo o rechazarlo.</p>
                </div>
            </div>
        </div>
    </div>
</div>
