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
    // Nuevos: ver anteriores y búsqueda por grupo
    public $verAnteriores = false;
    public $grupo = '';

    public function mount(): void
    {
        // Inicializar fechas a hoy si no fueron provistas
        $today = now()->toDateString();
        $this->fechaDesde = $this->fechaDesde ?: $today;
        $this->fechaHasta = $this->fechaHasta ?: $today;
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

    public function updatedVerAnteriores(): void
    {
        // al cambiar el checkbox, resetear paginación y búsquedas previas
        $this->resetPage();
        if (! $this->verAnteriores) {
            // si desactivó ver anteriores, limpiar grupo
            $this->grupo = '';
        }
    }

    public function updatingGrupo(): void
    {
        // cuando el usuario escribe el grupo en tiempo real, resetear paginación
        $this->resetPage();
    }

    public function buscarPorGrupo(): void
    {
        // Forzar modo "ver anteriores" y recargar
        $this->verAnteriores = true;
        $this->resetPage();
    }

    public function resetToToday(): void
    {
        $today = now()->toDateString();
        $this->fechaDesde = $today;
        $this->fechaHasta = $today;
        $this->verAnteriores = false;
        $this->grupo = '';
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

        // Si está activado ver anteriores, omitimos filtro por fecha_entrega
        if ($this->verAnteriores) {
            // Si ingresaron un grupo, buscar por id (grupo) o folio
            if (!empty($this->grupo)) {
                $query->where(function ($q) {
                    $q->where('id', $this->grupo)
                        ->orWhere('folio', $this->grupo);
                });
            }
            // si verAnteriores está activo y no hay grupo, no aplicamos filtro por fecha
        } else {
            // Filtro por fecha de entrega: por defecto ambas fechas serán hoy
            if (!empty($this->fechaDesde)) {
                $query->whereDate('fecha_entrega', '>=', $this->fechaDesde);
            }

            if (!empty($this->fechaHasta)) {
                $query->whereDate('fecha_entrega', '<=', $this->fechaHasta);
            }
        }

        $prestamos = $query->orderByDesc('updated_at')->paginate($this->perPage);

        return view('livewire.prestamos.autorizados', compact('prestamos'));
    }
}
