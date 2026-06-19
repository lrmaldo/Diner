<?php

namespace App\Livewire\Roles;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    public Role $role;

    public $name = '';

    public $selectedPermissions = [];

    public $esRolProtegido = false;

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->esRolProtegido = in_array($role->name, Index::ROLES_PROTEGIDOS, true);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->role->id)],
            'selectedPermissions' => ['required', 'array', 'min:1'],
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

        if ($this->esRolProtegido) {
            // El nombre y los permisos de los roles base del sistema no se modifican desde aquí.
            session()->flash('status', 'Este rol es parte del sistema; no se modificaron sus datos.');

            return redirect()->route('roles.index');
        }

        $this->role->update(['name' => $this->name]);
        $this->role->syncPermissions($this->selectedPermissions);

        session()->flash('status', 'Rol actualizado exitosamente.');

        return redirect()->route('roles.index');
    }

    public function render()
    {
        $permisosAgrupados = Permission::all()->groupBy(function ($permission) {
            $partes = explode(' ', $permission->name, 2);
            return $partes[1] ?? $partes[0];
        });

        return view('livewire.roles.edit', [
            'permisosAgrupados' => $permisosAgrupados,
        ]);
    }
}
