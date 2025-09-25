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
    public $filterEstado = '';
    public $filterMunicipio = '';

    protected $updatesQueryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMunicipio(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filterEstado = '';
        $this->filterMunicipio = '';
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        // Verificar permiso a nivel de backend
        if (! auth()->user() || ! auth()->user()->can('eliminar clientes')) {
            session()->flash('error', 'No tienes permiso para eliminar clientes.');
            return;
        }

        $cliente = Cliente::find($id);
        if (! $cliente) {
            session()->flash('error', 'Cliente no encontrado');
            return;
        }

        $cliente->delete();
        session()->flash('success', 'Cliente eliminado correctamente');
        $this->resetPage();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $clientes = Cliente::query()
            ->when($this->search, function ($q) {
                $q->where('nombres', 'like', "%{$this->search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                  ->orWhere('apellido_materno', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('curp', 'like', "%{$this->search}%");
            })
            ->when($this->filterEstado, function ($q) {
                $q->where('estado', $this->filterEstado);
            })
            ->when($this->filterMunicipio, function ($q) {
                $q->where('municipio', $this->filterMunicipio);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        // Valores para filtros y cards (precalcular para evitar lógica en Blade)
        $estados = Cliente::select('estado')->distinct()->pluck('estado')->filter()->values();
        $municipios = Cliente::when($this->filterEstado, function ($q) {
            $q->where('estado', $this->filterEstado);
        })->select('municipio')->distinct()->pluck('municipio')->filter()->values();

        $totalClientes = Cliente::count();
        $conTelefono = Cliente::has('telefonos')->count();
        $conEmail = Cliente::whereNotNull('email')->count();

        return view('livewire.clients.index', [
            'clientes' => $clientes,
            'estados' => $estados,
            'municipios' => $municipios,
            'totalClientes' => $totalClientes,
            'conTelefono' => $conTelefono,
            'conEmail' => $conEmail,
        ]);
    }
}
