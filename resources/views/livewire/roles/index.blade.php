<div>
    <x-slot name="title">Roles y Permisos | Diner</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Roles y Permisos</h2>
                        <a href="{{ route('roles.create') }}" class="btn-primary">
                            Nuevo Rol
                        </a>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 mb-6">
                        <div class="flex-1">
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Buscar roles..."
                                class="w-full py-3 px-4 h-12 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            />
                        </div>
                    </div>

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

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permisos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($roles as $role)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $role->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $role->name }}
                                            @if(in_array($role->name, $rolesProtegidos))
                                                <span class="ml-2 badge-project admin">Rol del sistema</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $role->permissions_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $role->users_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                {{ in_array($role->name, $rolesProtegidos) ? 'Ver' : 'Editar' }}
                                            </a>

                                            @if(!in_array($role->name, $rolesProtegidos))
                                                <button wire:click="confirmRoleDeletion({{ $role->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay roles que coincidan con la búsqueda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($showDeleteModal)
        <div class="modal-backdrop"></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="modal-panel w-full max-w-lg mx-4">
                <div class="bg-white dark:bg-gray-800 px-6 py-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-project-100 dark:bg-project-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-project-700 dark:text-project-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-200">Eliminar Rol</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que deseas eliminar este rol? Esta acción no se puede deshacer.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button wire:click="cancelDelete" type="button" class="btn-outline">Cancelar</button>
                        <button wire:click="deleteRole" type="button" class="btn-danger">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
