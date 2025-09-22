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
    public User $user;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|email|max:255')]
    public $email = '';

    public $password = '';
    public $password_confirmation = '';

    #[Rule('required|array|min:1')]
    public $selectedRoles = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();

        // Validación de email única excepto para este usuario
        $this->rules['email'] = [
            'required', 'string', 'email', 'max:255',
            "unique:users,email,{$user->id}"
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // Solo actualizar la contraseña si se proporciona una nueva
        if (!empty($this->password)) {
            $this->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        // Sincronizar roles
        $this->user->syncRoles([]);
        foreach ($this->selectedRoles as $roleId) {
            $role = Role::findById($roleId);
            $this->user->assignRole($role);
        }

        $this->dispatch('user-updated');

        session()->flash('status', 'Usuario actualizado exitosamente.');

        return redirect()->route('users.index');
    }

    #[Computed]
    public function allRoles()
    {
        return Role::all();
    }
}; ?>

<div>
    @title('Editar Usuario | Diner')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Editar Usuario</h2>
                        <p class="text-gray-600 mt-1">Actualice la información del usuario.</p>
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

                        <!-- Contraseña (opcional) -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Nueva Contraseña <span class="text-gray-500 text-xs">(Dejar en blanco para mantener la actual)</span>
                            </label>
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
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
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
                                            {{ $user->isAdmin() && $role->name == 'Administrador' ? 'disabled' : '' }}
                                        >
                                        <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-900">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('selectedRoles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @if($user->isAdmin())
                                <p class="mt-2 text-sm text-amber-600">
                                    El rol Administrador no puede ser removido para este usuario.
                                </p>
                            @endif
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
                                Actualizar Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
