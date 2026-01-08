<?php

namespace App\Livewire\Prestamos;

use App\Models\Prestamo;
use Livewire\Component;
use Livewire\WithPagination;

class Rechazados extends Component
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

    public function reenviarAComite($id)
    {
        $prestamo = Prestamo::findOrFail($id);
        
        // Verificar que esté rechazado
        if ($prestamo->estado !== 'rechazado') {
            $this->dispatch('toast', message: 'El préstamo no está en estado rechazado.', type: 'error');
            return;
        }

        $prestamo->estado = 'en_comite';
        $prestamo->save();

        // Registrar en bitácora si existe el método
        if (method_exists($prestamo, 'registrarBitacora')) {
            $prestamo->registrarBitacora('en_comite', 'Reenviado a comité desde rechazados');
        }

        $this->dispatch('toast', message: 'Préstamo reenviado a comité exitosamente.', type: 'success');
    }

    public function eliminarPrestamo($id)
    {
        $prestamo = Prestamo::findOrFail($id);

        if ($prestamo->estado !== 'rechazado') {
            $this->dispatch('toast', message: 'El préstamo no está en estado rechazado.', type: 'error');
            return;
        }

        $prestamo->delete();

        $this->dispatch('toast', message: 'Préstamo eliminado correctamente.', type: 'success');
    }

    public function render()
    {
        $query = Prestamo::query()
            ->with(['cliente', 'representante'])
            ->where('estado', 'rechazado'); // Solo préstamos rechazados

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

        // Filtro por fecha desde (usando updated_at para ver cuándo fue rechazado)
        if (! empty($this->fechaDesde)) {
            $query->whereDate('updated_at', '>=', $this->fechaDesde);
        }

        // Filtro por fecha hasta
        if (! empty($this->fechaHasta)) {
            $query->whereDate('updated_at', '<=', $this->fechaHasta);
        }

        // Si el usuario es asesor, mostrar solo los préstamos asignados a él
        if (auth()->user()->hasRole('Asesor')) {
            $query->where('asesor_id', auth()->id());
        }

        $prestamos = $query->orderByDesc('updated_at')->paginate($this->perPage);

        return view('livewire.prestamos.rechazados', compact('prestamos'));
    }
}
