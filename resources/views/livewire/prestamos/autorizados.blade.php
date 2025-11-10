<div class="p-4 max-w-full mx-auto" wire:poll.visible.10s>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold">Préstamos Autorizados</h1>
        <div class="flex gap-2 flex-wrap items-center">
            @if(auth()->user()->hasRole('Administrador'))
                <a href="{{ route('prestamos.en-comite') }}" class="btn-outline text-center">En comité</a>
            @endif
            <a href="{{ route('prestamos.index') }}" class="btn-outline text-center">Ver todos</a>
            <div class="text-sm text-gray-600">
                Total: {{ $prestamos->total() }} préstamo{{ $prestamos->total() !== 1 ? 's' : '' }}
            </div>
        </div>
    </div>

    {{-- Controles de filtrado --}}
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            {{-- Botón Créditos del día --}}
            <button
                wire:click="resetToToday"
                class="inline-flex items-center justify-center px-4 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Créditos del día
            </button>

            {{-- Checkbox Ver créditos anteriores --}}
            <label class="inline-flex items-center cursor-pointer group">
                <div class="relative">
                    <input
                        type="checkbox"
                        wire:model.live="verAnteriores"
                        class="sr-only peer" />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </div>
                <span class="ml-3 text-sm font-medium text-gray-700 group-hover:text-gray-900">
                    Ver créditos anteriores
                </span>
            </label>

            {{-- Input de número de grupo (solo visible cuando verAnteriores está activo) --}}
            @if($verAnteriores)
                <div class="flex-1 md:max-w-xs transition-all duration-300 ease-in-out">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            wire:model.live.debounce.500ms="grupo"
                            type="text"
                            placeholder="Número de grupo"
                            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" />
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        @if($prestamos->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Grupo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Tipo de producto</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Representante</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Crédito / Monto</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Integrantes</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Fecha de entrega</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700">Plazo</th>
                            <th class="px-3 py-3 text-sm font-medium text-gray-700 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prestamos as $p)
                            <tr class="border-t hover:bg-gray-50 transition-colors">
                                <!-- Grupo (ID del préstamo) -->
                                <td class="px-3 py-3 font-medium text-sm">{{ $p->id }}</td>

                                <!-- Tipo de producto -->
                                <td class="px-3 py-3 text-sm">
                                    <span class="capitalize">{{ $p->producto ?? 'N/A' }}</span>
                                </td>

                                <!-- Representante -->
                                <td class="px-3 py-3 text-sm">
                                    @php $representante = $p->representante; @endphp
                                    @if($representante)
                                        {{ trim(($representante->nombres ?? '').' '.($representante->apellido_paterno ?? '')) }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- Crédito / Monto -->
                                <td class="px-3 py-3 font-medium text-sm">
                                    ${{ number_format($p->monto_total ?? 0, 2) }}
                                </td>

                                <!-- Integrantes -->
                                <td class="px-3 py-3 text-sm text-center">
                                    @if($p->producto === 'grupal')
                                        {{ $p->clientes->count() }}
                                    @else
                                        1
                                    @endif
                                </td>

                                <!-- Fecha de entrega -->
                                <td class="px-3 py-3 text-sm">
                                    {{ $p->fecha_entrega ? $p->fecha_entrega->format('d/m/Y h:i a') : '—' }}
                                </td>

                                <!-- Plazo -->
                                <td class="px-3 py-3 text-sm">{{ $p->plazo }} </td>

                                <!-- Acciones -->
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Detalle (PDF) -->
                                                     <a href="{{ route('prestamos.print', ['prestamo' => $p->id, 'type' => 'detalle']) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l-3-3m3 3l3-3" />
                                            </svg>
                                            Detalle (PDF)
                                        </a>

                                        <!-- Pagaré (PDF) -->
                                                     <a href="{{ route('prestamos.print', ['prestamo' => $p->id, 'type' => 'pagare']) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center px-3 py-1 border border-blue-300 shadow-sm text-xs font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h10M7 16h10" />
                                            </svg>
                                            Pagaré (PDF)
                                        </a>

                                        <!-- Calendario (PDF) -->
                                                     <a href="{{ route('prestamos.print', ['prestamo' => $p->id, 'type' => 'calendario']) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center px-3 py-1 border border-green-300 shadow-sm text-xs font-medium rounded-md text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Calendario
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
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay préstamos autorizados</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(!empty($this->search) || !empty($this->producto) || !empty($this->fechaDesde) || !empty($this->fechaHasta))
                        No se encontraron préstamos autorizados que coincidan con los filtros aplicados.
                    @else
                        Aún no hay préstamos autorizados en el sistema.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
