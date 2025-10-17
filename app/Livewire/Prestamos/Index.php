<?php

namespace App\Livewire\Prestamos;

use App\Models\Prestamo;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Modales y detalles
    public $showModalDetalle = false;

    public $prestamoIdRechazar = null;

    public $prestamoIdVerMotivo = null;

    public $motivoRechazo = '';

    public $prestamoSeleccionado = null;

    // Búsqueda y filtros
    public $search = '';

    public $estado = '';

    public $fechaDesde = '';

    public $fechaHasta = '';

    public $producto = '';

    public $perPage = 15;

    public function render()
    {
        $query = Prestamo::with(['cliente', 'clientes', 'representante']);

        // Aplicar filtros de búsqueda
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('folio', 'like', '%'.$this->search.'%')
                    ->orWhereHas('cliente', function ($q) {
                        $q->where('nombres', 'like', '%'.$this->search.'%')
                            ->orWhere('apellido_paterno', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('representante', function ($q) {
                        $q->where('nombres', 'like', '%'.$this->search.'%')
                            ->orWhere('apellido_paterno', 'like', '%'.$this->search.'%');
                    });
            });
        }

        // Filtro por estado
        if (! empty($this->estado)) {
            $query->where('estado', $this->estado);
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

        $prestamos = $query->orderByDesc('created_at')->paginate($this->perPage);

        return view('livewire.prestamos.index', compact('prestamos'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingProducto()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'estado', 'fechaDesde', 'fechaHasta', 'producto']);
        $this->resetPage();
    }

    public function autorizar(int $id)
    {
        $this->authorizeAction();
        $p = Prestamo::findOrFail($id);
        $p->autorizar(auth()->user());
        session()->flash('success', "Préstamo #{$id} autorizado");
        $this->dispatch('refreshComponent');
    }

    public function rechazar()
    {
        $this->authorizeAction();

        if (! $this->prestamoIdRechazar || empty(trim($this->motivoRechazo))) {
            session()->flash('error', 'Se requiere un motivo para rechazar el préstamo');

            return;
        }

        $p = Prestamo::findOrFail($this->prestamoIdRechazar);
        $p->rechazar(auth()->user(), $this->motivoRechazo);

        $this->prestamoIdRechazar = null;
        $this->motivoRechazo = '';

        session()->flash('success', 'Préstamo rechazado correctamente');
        $this->dispatch('refreshComponent');
    }

    public function confirmarRechazo(int $id)
    {
        $this->authorizeAction();
        $this->prestamoIdRechazar = $id;
    }

    public function verDetallesPrestamo(int $id)
    {
        $this->prestamoSeleccionado = Prestamo::with(['cliente', 'clientes', 'representante'])->findOrFail($id);
        $this->showModalDetalle = true;
    }

    public function verMotivoRechazo(int $id)
    {
        $this->prestamoSeleccionado = Prestamo::findOrFail($id);
        $this->prestamoIdVerMotivo = $id;
    }

    public function cerrarModales()
    {
        $this->reset([
            'showModalDetalle',
            'prestamoIdRechazar',
            'prestamoIdVerMotivo',
            'motivoRechazo',
            'prestamoSeleccionado',
        ]);
    }

    public function enviarARevision(int $id)
    {
        $prestamo = Prestamo::findOrFail($id);
        $prestamo->estado = 'en_comite';
        $prestamo->save();
        session()->flash('success', 'Préstamo enviado a comité');
        $this->dispatch('refreshComponent');
    }

    protected function authorizeAction(): void
    {
        if (! auth()->user() || ! auth()->user()->hasRole('Administrador')) {
            abort(403);
        }
    }
}
