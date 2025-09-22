@php
    // Esta vista ahora es consumida por el componente Livewire tradicional
    // `App\Livewire\Users\Create` que pasa la variable `$allRoles`.
@endphp

<div>
    @title('Crear Usuario | Diner')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Crear Nuevo Usuario</h2>
                        <p class="text-gray-600 mt-1">Complete el formulario para crear un nuevo usuario.</p>
                    </div>

                    <form wire:submit.prevent="save" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input
                                wire:model="name"
                                type="text"
                                id="name"
                                class="input-project mt-1"
                            >
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                            <input
                                wire:model="email"
                                type="email"
                                id="email"
                                class="input-project mt-1"
                            >
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                            <input
                                wire:model="password"
                                type="password"
                                id="password"
                                class="input-project mt-1"
                            >
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                            <input
                                wire:model="password_confirmation"
                                type="password"
                                id="password_confirmation"
                                class="input-project mt-1"
                            >
                        </div>

                        <!-- Roles -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asignar Roles</label>
                            <div class="mt-2 space-y-2">
                                @foreach($allRoles as $role)
                                    <div class="flex items-center">
                                        <input
                                            wire:model="selectedRoles"
                                            id="role_{{ $role->id }}"
                                            type="checkbox"
                                            value="{{ $role->id }}"
                                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        >
                                        <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-900">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedRoles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3">
                            <a
                                href="{{ route('users.index') }}"
                                class="btn-outline"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                class="btn-primary"
                            >
                                Guardar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
