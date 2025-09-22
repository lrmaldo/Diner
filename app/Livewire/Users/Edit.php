<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Role;
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

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
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

        $this->dispatchBrowserEvent('user-updated');

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
