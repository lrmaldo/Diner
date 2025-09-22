<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts.app')] class extends Component {
    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|email|max:255|unique:users,email')]
    public $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public $password = '';

    #[Rule('required|string|min:8')]
    public $password_confirmation = '';

    #[Rule('required|array|min:1')]
    public $selectedRoles = [];

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        // Asignar roles seleccionados
        foreach ($this->selectedRoles as $roleId) {
            $role = Role::findById($roleId);
            $user->assignRole($role);
        }

        $this->dispatch('user-created');

        session()->flash('status', 'Usuario creado exitosamente.');

        return redirect()->route('users.index');
    }

    #[Computed]
    public function allRoles()
    {
        return Role::all();
    }
}; ?>

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

                    <form wire:submit="save" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input
                                wire:model="name"
                                type="text"
                                id="name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            >
                        </div>

                        <!-- Roles -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Asignar Roles</label>
                            <div class="mt-2 space-y-2">
                                @foreach($this->allRoles as $role)
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
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
