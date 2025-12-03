<?php

namespace App\Livewire\Clients;

use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $perPage = 10;

    public $filterEstado = '';

    public $filterMunicipio = '';

    public $confirmingDeleteId = null;

    public ?string $statusMessage = null;

    public ?string $statusType = null; // success | error

    public int $statusTimeout = 5000; // ms

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

    /**
     * Request confirmation before deleting a cliente.
     */
    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelConfirmDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function dismissStatus(): void
    {
        $this->statusMessage = null;
        $this->statusType = null;
    }

    /**
     * Perform the deletion after user confirms in the modal.
     */
    public function deleteConfirmed(): void
    {
        $id = $this->confirmingDeleteId;
        if (! $id) {
            return;
        }

        // Verificar permiso a nivel de backend
        if (! auth()->user() || ! auth()->user()->can('eliminar clientes')) {
            $this->statusType = 'error';
            $this->statusMessage = 'No tienes permiso para eliminar clientes.';
            $this->confirmingDeleteId = null;

            return;
        }

        $cliente = Cliente::find($id);
        if (! $cliente) {
            $this->statusType = 'error';
            $this->statusMessage = 'Cliente no encontrado.';
            $this->confirmingDeleteId = null;

            return;
        }

        $cliente->delete();

        $this->confirmingDeleteId = null;
        $this->resetPage();

        // Feedback inline
        $this->statusType = 'success';
        $this->statusMessage = 'Cliente eliminado correctamente';
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
