<?php

namespace App\Livewire\Users;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public $name = '';

    public $email = '';

    public $password = '';

    public $password_confirmation = '';

    public $selectedRoles = [];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('id')->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'selectedRoles' => ['required', 'array', 'min:1'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'selectedRoles.required' => 'Debe asignar al menos un rol.',
            'selectedRoles.min' => 'Debe asignar al menos un rol.',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        $this->user->update($data);

        // Sincronizar roles
        $this->user->syncRoles([]);
        foreach ($this->selectedRoles as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $this->user->assignRole($role->name);
            }
        }

        $this->dispatch('user-updated');

        session()->flash('status', 'Usuario actualizado exitosamente.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.edit', [
            'allRoles' => Role::all(),
        ]);
    }
}
