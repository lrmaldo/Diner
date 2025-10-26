<?php

namespace App\Livewire\Prestamos;

use App\Models\Prestamo;
use Livewire\Component;
use Livewire\WithPagination;

class EnComite extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public $search = '';

    public $producto = '';

    public $fechaDesde = '';

    public $fechaHasta = '';

    public $perPage = 10;

    public function mount()
    {
        // Permitir acceso a Administradores y Asesores
        if (! auth()->user()->hasRole('Administrador') && ! auth()->user()->hasRole('Asesor')) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProducto(): void
    {
        $this->resetPage();
    }

    public function updatingFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Prestamo::query()
            ->with(['cliente', 'representante', 'clientes'])
            ->where('estado', 'en_comite'); // Solo préstamos en comité

        // Búsqueda por folio o nombre del cliente
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('cliente', function ($q2) {
                        $q2->where('nombres', 'like', "%{$this->search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                            ->orWhere('apellido_materno', 'like', "%{$this->search}%");
                    });
            });
        }

        // Filtro por tipo de producto
        if (! empty($this->producto)) {
            $query->where('producto', $this->producto);
        }

        // Filtro por fecha desde
        if (! empty($this->fechaDesde)) {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }

        // Filtro por fecha hasta
        if (! empty($this->fechaHasta)) {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        // Si el usuario es asesor, mostrar solo los préstamos asignados a él
        if (auth()->user()->hasRole('Asesor')) {
            $query->where('asesor_id', auth()->id());
        }

        $prestamos = $query->orderByDesc('created_at')->paginate($this->perPage);

        return view('livewire.prestamos.en-comite', compact('prestamos'));
    }
}
