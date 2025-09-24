<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;

    protected $updatesQueryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $clientes = Cliente::query()
            ->when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('apellido', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('curp', 'like', "%{$this->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.clients.index', [
            'clientes' => $clientes,
        ]);
    }
}
