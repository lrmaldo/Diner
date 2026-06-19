<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use Livewire\Component;

class Create extends Component
{
    public $name = '';

    public $selectedPermissions = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'required|array|min:1',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'name.unique' => 'Ya existe un rol con ese nombre.',
            'selectedPermissions.required' => 'Debe asignar al menos un permiso.',
            'selectedPermissions.min' => 'Debe asignar al menos un permiso.',
        ];
    }

    public function save()
    {
        $this->validate();

        $role = Role::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('status', 'Rol creado exitosamente.');

        return redirect()->route('roles.index');
    }

    public function render()
    {
        $permisosAgrupados = Permission::all()->groupBy(function ($permission) {
            $partes = explode(' ', $permission->name, 2);
            return $partes[1] ?? $partes[0];
        });

        return view('livewire.roles.create', [
            'permisosAgrupados' => $permisosAgrupados,
        ]);
    }
}
