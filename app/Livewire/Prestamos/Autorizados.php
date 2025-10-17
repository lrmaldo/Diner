<?php

namespace App\Livewire\Prestamos;

use App\Models\Prestamo;
use Livewire\Component;
use Livewire\WithPagination;

class Autorizados extends Component
{
    use WithPagination;

    // Búsqueda y filtros
    public $search = '';
    public $producto = '';
    public $fechaDesde = '';
    public $fechaHasta = '';
    public $perPage = 10;

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
            ->with(['cliente', 'representante', 'autorizador'])
            ->where('estado', 'autorizado'); // Solo préstamos autorizados

        // Búsqueda por folio o nombre del cliente
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('cliente', function ($q2) {
                        $q2->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('apellido_paterno', 'like', "%{$this->search}%")
                            ->orWhere('apellido_materno', 'like', "%{$this->search}%");
                    });
            });
        }

        // Filtro por tipo de producto
        if (!empty($this->producto)) {
            $query->where('producto', $this->producto);
        }

        // Filtro por fecha desde
        if (!empty($this->fechaDesde)) {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }

        // Filtro por fecha hasta
        if (!empty($this->fechaHasta)) {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        $prestamos = $query->orderByDesc('updated_at')->paginate($this->perPage);

        return view('livewire.prestamos.autorizados', compact('prestamos'));
    }
}
