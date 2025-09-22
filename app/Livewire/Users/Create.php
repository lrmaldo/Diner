<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Create extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $selectedRoles = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'selectedRoles' => 'required|array|min:1',
        ];
    }

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        foreach ($this->selectedRoles as $roleId) {
            $role = Role::find($roleId);
            if ($role) {
                $user->assignRole($role->name);
            }
        }

        $this->dispatchBrowserEvent('user-created');

        session()->flash('status', 'Usuario creado exitosamente.');

        return redirect()->route('users.index');
    }

    public function render()
    {
        $allRoles = Role::all();

        return view('livewire.users.create', compact('allRoles'));
    }
}
