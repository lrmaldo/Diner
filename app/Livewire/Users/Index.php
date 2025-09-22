<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $roleFilter = '';
    public $showDeleteModal = false;
    public $userIdToDelete = null;

    protected $listeners = ['user-created' => 'refreshUsers', 'user-updated' => 'refreshUsers'];

    public function refreshUsers()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmUserDeletion($userId)
    {
        $this->userIdToDelete = $userId;
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->userIdToDelete) {
            $user = User::find($this->userIdToDelete);

            if ($user && !$user->isAdmin()) {
                $user->delete();
                session()->flash('status', 'Usuario eliminado exitosamente.');
            } else {
                session()->flash('error', 'No se puede eliminar este usuario.');
            }

            $this->showDeleteModal = false;
            $this->userIdToDelete = null;
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->userIdToDelete = null;
    }

    public function render()
    {
        $users = User::query()
            ->where('name', 'like', "%{$this->search}%")
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('name', $this->roleFilter);
                });
            })
            ->orderBy('id')
            ->paginate($this->perPage);

        $allRoles = Role::all();

        return view('livewire.users.index', [
            'users' => $users,
            'allRoles' => $allRoles
        ]);
    }
}
