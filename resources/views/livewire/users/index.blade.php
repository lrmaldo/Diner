

<div>
    <x-slot name="title">Gestión de Usuarios | Diner</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Usuarios</h2>
                        @can('crear usuarios')
                            <a href="{{ route('users.create') }}" class="btn-primary">
                                Nuevo Usuario
                            </a>
                        @endcan
                    </div>

                    <!-- Filtros y búsqueda -->
                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Buscar usuarios..."
                                class="w-full py-3 px-4 h-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                        </div>
                        <div class="w-full md:w-1/4">
                            <select
                                wire:model.live="roleFilter"
                                class="w-full py-3 px-4 h-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="">Todos los roles</option>
                                @foreach($allRoles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-1/6">
                            <select
                                wire:model.live="perPage"
                                class="w-full py-3 px-4 h-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                                <option value="10">10 por página</option>
                                <option value="25">25 por página</option>
                                <option value="50">50 por página</option>
                                <option value="100">100 por página</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mensajes de estado -->
                    @if (session('status'))
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Tabla de usuarios -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @foreach($user->roles as $role)
                                                @php $badgeClass = $role->name == 'Administrador' ? 'badge-project admin' : 'badge-project default'; @endphp
                                                <span class="{{ $badgeClass }}">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $user->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @can('editar usuarios')
                                                <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</a>
                                            @endcan

                                            @can('eliminar usuarios')
                                                @if(!$user->isAdmin())
                                                    <button wire:click="confirmUserDeletion({{ $user->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay usuarios que coincidan con los filtros.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    @if($showDeleteModal)
        <div class="modal-backdrop"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="modal-panel w-full max-w-lg mx-4">
                <div class="bg-white px-6 py-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-project-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-project-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Eliminar Usuario</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button wire:click="cancelDelete" type="button" class="btn-outline">Cancelar</button>
                        <button wire:click="deleteUser" type="button" class="btn-danger">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
