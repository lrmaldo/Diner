<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-project-700">Clientes</h1>
        <a href="{{ route('clients.create') }}" class="btn-primary">Nuevo cliente</a>
    </div>

    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
                <div class="flex items-center gap-2 w-full">
                <input wire:model.live="search" type="text" placeholder="Buscar por nombre, email o CURP" class="input-project" />
            </div>
            <div class="mt-3 flex gap-2">
                <select wire:model.live="filterEstado" class="input-project select-project w-48">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $est)
                        <option value="{{ $est }}">{{ $est }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterMunicipio" class="input-project select-project w-48">
                    <option value="">Todos los municipios</option>
                    @foreach($municipios as $mun)
                        <option value="{{ $mun }}">{{ $mun }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="md:col-span-1">
                <div class="grid grid-cols-3 gap-3">
                    <div class="stat-card text-center">
                        <div class="value">{{ $totalClientes }}</div>
                        <div class="label">Total clientes</div>
                    </div>
                    <div class="stat-card text-center">
                        <div class="value">{{ $conTelefono }}</div>
                        <div class="label">Con teléfono</div>
                    </div>
                    <div class="stat-card text-center">
                        <div class="value">{{ $conEmail }}</div>
                        <div class="label">Con email</div>
                    </div>
                </div>
        </div>
    </div>

    <div class="overflow-x-auto bg-white shadow rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre completo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CURP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Municipio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código postal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Teléfono</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse ($clientes as $cliente)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente->id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ trim(implode(' ', array_filter([$cliente->apellido_paterno, $cliente->apellido_materno, $cliente->nombres]))) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $cliente->curp ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->email ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->municipio ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->codigo_postal ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($cliente->telefonos->first())->numero ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('clients.show', $cliente) }}" class="text-project-500 hover:underline mr-3">Ver</a>
                            <a href="{{ route('clients.edit', $cliente) }}" class="text-project-700 hover:underline mr-3">Editar</a>
                            @if(auth()->user() && auth()->user()->can('eliminar clientes'))
                                <button wire:click.prevent="confirmDelete({{ $cliente->id }})" class="text-red-600 hover:underline">Eliminar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center">
                            <div class="max-w-xl mx-auto">
                                <svg class="mx-auto mb-4" width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 21l-4.35-4.35" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15z" stroke="#ef4444" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-800">No se encontraron clientes</h3>
                                <p class="text-sm text-gray-500 mt-2">Prueba con otros términos de búsqueda o restablece los filtros.</p>
                                <div class="mt-4 flex justify-center gap-2">
                                    <button wire:click.prevent="clearSearch" class="btn-outline">Limpiar búsqueda</button>
                                    <button wire:click.prevent="clearFilters" class="btn-primary">Restablecer filtros</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $clientes->links() }}
    </div>
    {{-- Confirm delete modal --}}
    @if($confirmingDeleteId)
        <div class="fixed inset-0 z-40 flex items-center justify-center">
            <div class="fixed inset-0 bg-black opacity-30"></div>
            <div class="bg-white rounded-lg shadow-lg z-50 max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-800">Confirmar eliminación</h3>
                <p class="text-sm text-gray-600 mt-2">¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer.</p>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click.prevent="cancelConfirmDelete" class="btn-outline">Cancelar</button>
                    <button wire:click.prevent="deleteConfirmed" class="btn-danger">Eliminar</button>
                </div>
            </div>
        </div>
    @endif

</div>
