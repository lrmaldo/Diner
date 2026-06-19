<div>
    <x-slot name="title">Crear Rol | Diner</x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Crear Nuevo Rol</h2>
                        <p class="text-gray-600 mt-1">Define el nombre del rol y los permisos que tendrá.</p>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre del rol</label>
                            <input wire:model="name" type="text" id="name" class="input-project mt-1">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permisos</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($permisosAgrupados as $grupo => $permisos)
                                    <div class="border border-gray-200 rounded-md p-4">
                                        <h4 class="text-sm font-semibold text-gray-800 uppercase mb-2 capitalize">{{ $grupo }}</h4>
                                        <div class="space-y-2">
                                            @foreach($permisos as $permiso)
                                                <div class="flex items-center">
                                                    <input
                                                        wire:model="selectedPermissions"
                                                        id="permiso_{{ $permiso->id }}"
                                                        type="checkbox"
                                                        value="{{ $permiso->id }}"
                                                        class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                                    >
                                                    <label for="permiso_{{ $permiso->id }}" class="ml-2 block text-sm text-gray-900">
                                                        {{ $permiso->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedPermissions') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('roles.index') }}" class="btn-outline">Cancelar</a>
                            <button type="submit" class="btn-primary">Guardar Rol</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
