<?php

namespace App\Livewire\Roles;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public const ROLES_PROTEGIDOS = ['Administrador', 'Cajero', 'Asesor'];

    public $search = '';

    public $showDeleteModal = false;

    public $roleIdToDelete = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmRoleDeletion($roleId)
    {
        $this->roleIdToDelete = $roleId;
        $this->showDeleteModal = true;
    }

    public function deleteRole()
    {
        $role = Role::find($this->roleIdToDelete);

        if ($role) {
            if (in_array($role->name, self::ROLES_PROTEGIDOS, true)) {
                session()->flash('error', 'Este rol es parte del sistema y no se puede eliminar.');
            } elseif ($role->users()->count() > 0) {
                session()->flash('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
            } else {
                $role->delete();
                session()->flash('status', 'Rol eliminado exitosamente.');
            }
        }

        $this->showDeleteModal = false;
        $this->roleIdToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->roleIdToDelete = null;
    }

    public function render()
    {
        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->where('name', 'like', "%{$this->search}%")
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.roles.index', [
            'roles' => $roles,
            'rolesProtegidos' => self::ROLES_PROTEGIDOS,
        ]);
    }
}
