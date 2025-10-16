<div class="p-4 max-w-full mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-semibold">Préstamos</h1>
        <a href="{{ route('prestamos.create') }}" class="btn-primary text-center">Solicitar crédito</a>
    </div>

    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
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

            <!-- Filtro por estado -->
            <div>
                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select wire:model.live="estado" id="estado" class="input-project">
                    <option value="">Todos</option>
                    <option value="en_curso">En curso</option>
                    <option value="en_revision">En revisión</option>
                    <option value="autorizado">Autorizado</option>
                    <option value="rechazado">Rechazado</option>
                </select>
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

        <!-- Filtros por fecha -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="fechaDesde" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input wire:model.live="fechaDesde" type="date" id="fechaDesde" class="input-project">
            </div>

            <div>
                <label for="fechaHasta" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input wire:model.live="fechaHasta" type="date" id="fechaHasta" class="input-project">
            </div>

            <div class="flex items-end">
                <button wire:click="resetFilters" class="btn-outline w-full">
                    <i class="fas fa-undo mr-1"></i> Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg p-4 overflow-x-auto">
        <table class="w-full table-auto min-w-max">
            <thead>
                <tr class="text-left border-b">
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Folio</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Fecha de entrega</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Tipo de Producto</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Integrantes</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Monto</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Representante</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Plazo</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700">Estatus</th>
                    <th class="px-3 py-3 text-sm font-medium text-gray-700 text-right" style="min-width: 250px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $p)
                    <tr class="border-t hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-3 font-medium text-sm">{{ $p->id }}</td>
                        <td class="px-3 py-3 text-sm">{{ $p->fecha_entrega ? $p->fecha_entrega->format('d/m/Y') : '—' }}</td>
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
                        <td class="px-3 py-3 text-sm">{{ $p->plazo }}</td>
                        <td class="px-3 py-3 text-sm">
                            @php
                                $estado = $p->estado;
                                $map = [
                                    'en_curso' => 'bg-yellow-100 text-yellow-800',
                                    'en_revision' => 'bg-blue-100 text-blue-800',
                                    'autorizado' => 'bg-green-100 text-green-800',
                                    'rechazado' => 'bg-red-100 text-red-800',
                                ];
                                $cls = $map[$estado] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-xs font-medium whitespace-nowrap {{ $cls }}">
                                {{ str_replace('_', ' ', $estado) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <div class="flex justify-end gap-2 flex-wrap">
                                {{-- Acciones para usuarios Administradores (comité) --}}
                                @if(auth()->user()->hasRole('Administrador'))
                                    <a href="{{ route('prestamos.show', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-eye mr-1"></i> Ver detalle
                                    </a>

                                    @if($p->estado === 'en_curso' || $p->estado === 'en_revision')
                                        <a href="{{ route('prestamos.edit', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                    @elseif($p->estado === 'rechazado')
                                        <a href="{{ route('prestamos.edit', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                    @endif

                                {{-- Acciones para usuarios Cajero --}}
                                @else
                                    @if($p->estado === 'en_curso')
                                        <a href="{{ route('prestamos.show', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <a href="{{ route('prestamos.edit', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Editar
                                        </a>
                                        <button wire:click.prevent="enviarARevision({{ $p->id }})" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-paper-plane mr-1"></i> Enviar a comité
                                        </button>
                                    @elseif($p->estado === 'en_revision')
                                        <a href="{{ route('prestamos.show', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <span class="text-xs text-gray-500 italic">En revisión por comité</span>
                                    @elseif($p->estado === 'rechazado')
                                        <a href="{{ route('prestamos.show', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <a href="{{ route('prestamos.edit', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Corregir
                                        </a>
                                        <button wire:click.prevent="verMotivoRechazo({{ $p->id }})" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            <i class="fas fa-info-circle mr-1"></i> Ver motivo
                                        </button>
                                    @elseif($p->estado === 'autorizado')
                                        <a href="{{ route('prestamos.show', $p->id) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-eye mr-1"></i> Ver
                                        </a>
                                        <span class="inline-flex items-center text-green-600 font-medium text-xs">
                                            <i class="fas fa-check-circle mr-1"></i> Aprobado
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            @if($prestamos->isEmpty())
                <div class="text-center py-4 text-gray-500">
                    <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-2">No se encontraron préstamos con los filtros actuales</p>
                    <button wire:click="resetFilters" class="btn-outline mt-2">
                        Limpiar filtros
                    </button>
                </div>
            @else
                {{ $prestamos->links() }}
            @endif
        </div>
    </div>

    {{-- Modal para ver detalles del préstamo --}}
    {{-- Recibir el evento de actualización --}}
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('refreshComponent', () => {
            // Este evento se lanzará desde el backend
            Livewire.dispatch('$refresh');
        });
    });
</script>

@if($showModalDetalle && $prestamoSeleccionado)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="p-5 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Detalles del Préstamo #{{ $prestamoSeleccionado->id }}
                    </h3>
                    <button type="button" wire:click="cerrarModales" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tipo de Producto</p>
                        <p class="capitalize">{{ $prestamoSeleccionado->producto }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">ID del Préstamo</p>
                        <p>{{ $prestamoSeleccionado->id }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Fecha de Solicitud</p>
                        <p>{{ $prestamoSeleccionado->created_at->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Monto Total</p>
                        <p class="font-semibold">${{ number_format($prestamoSeleccionado->monto_total ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Plazo</p>
                        <p>{{ $prestamoSeleccionado->plazo }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Periodicidad</p>
                        <p>{{ $prestamoSeleccionado->periodicidad }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Día de Pago</p>
                        <p class="capitalize">{{ $prestamoSeleccionado->dia_pago }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tasa de Interés</p>
                        <p>{{ number_format($prestamoSeleccionado->tasa_interes, 2) }}%</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 mt-2">
                    <h4 class="font-medium mb-2">{{ $prestamoSeleccionado->producto === 'grupal' ? 'Integrantes del Grupo' : 'Datos del Cliente' }}</h4>

                    @if($prestamoSeleccionado->producto === 'grupal')
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Representante</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($prestamoSeleccionado->clientes as $cliente)
                                        <tr>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                {{ trim(($cliente->nombres ?? '') . ' ' . ($cliente->apellido_paterno ?? '')) }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                ${{ number_format($cliente->pivot->monto_solicitado ?? 0, 2) }}
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                @if($prestamoSeleccionado->representante_id == $cliente->id)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        Representante
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nombre</p>
                                <p>{{ trim(($prestamoSeleccionado->cliente->nombres ?? '') . ' ' . ($prestamoSeleccionado->cliente->apellido_paterno ?? '')) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">CURP</p>
                                <p>{{ $prestamoSeleccionado->cliente->curp ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 border-t border-gray-200">
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cerrarModales" class="btn-outline">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal para rechazar préstamo --}}
    @if($prestamoIdRechazar)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Motivo de Rechazo
                </h3>
            </div>

            <div class="p-5">
                <div>
                    <label for="motivoRechazo" class="block text-sm font-medium text-gray-700 mb-1">
                        Por favor, indique el motivo del rechazo:
                    </label>
                    <textarea
                        id="motivoRechazo"
                        wire:model.defer="motivoRechazo"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-500 focus:ring-opacity-50"
                        rows="4"
                        placeholder="Escriba el motivo del rechazo"
                    ></textarea>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
                <button type="button" wire:click="cerrarModales" class="btn-outline">
                    Cancelar
                </button>
                <button type="button" wire:click="rechazar" class="btn-danger">
                    Confirmar Rechazo
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal para ver motivo de rechazo --}}
    @if($prestamoIdVerMotivo && $prestamoSeleccionado)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md">
            <div class="p-5 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Motivo de Rechazo - Préstamo #{{ $prestamoSeleccionado->id ?? 'N/A' }}
                    </h3>
                    <button type="button" wire:click="cerrarModales" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-5">
                <div class="bg-red-50 border border-red-200 rounded-md p-3">
                    <p class="text-red-800">
                        {{ $prestamoSeleccionado->motivo_rechazo ?? 'No se especificó un motivo de rechazo.' }}
                    </p>
                </div>
                <p class="mt-3 text-sm text-gray-600">
                    Revisado por: {{ optional($prestamoSeleccionado->autorizador)->name ?? 'Usuario desconocido' }}
                </p>
            </div>

            <div class="p-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button type="button" wire:click="cerrarModales" class="btn-outline">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
