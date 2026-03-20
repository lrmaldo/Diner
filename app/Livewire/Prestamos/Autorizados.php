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

    public $fechaSeleccionada = '';

    public $perPage = 10;

    // Búsqueda por grupo (opcional)
    public $grupo = '';

    public function mount(): void
    {
        // Inicializar fecha seleccionada a hoy (por defecto siempre hoy)
        $this->fechaSeleccionada = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingProducto(): void
    {
        $this->resetPage();
    }

    public function updatingGrupo(): void
    {
        // cuando el usuario escribe el grupo en tiempo real, resetear paginación
        $this->resetPage();
    }

    public function updatingFechaSeleccionada(): void
    {
        // Si el usuario cambia la fecha, resetear paginación
        // Esto permite visualizar cualquier día que el usuario elija
        $this->resetPage();
    }

    public function getFechasEventosProperty()
    {
        // Usar un enfoque más robusto sin selectRaw para evitar problemas de DB
        return Prestamo::query()
            ->select('fecha_entrega', 'estado') // Seleccionar columnas reales
            ->whereIn('estado', ['autorizado', 'entregado'])
            ->whereNotNull('fecha_entrega')
            ->get()
            ->groupBy(function($item) {
                // Usar Carbon directamente sobre la propiedad casted o parsear si es string
                if ($item->fecha_entrega instanceof \Carbon\Carbon) {
                    return $item->fecha_entrega->format('Y-m-d');
                }
                return \Carbon\Carbon::parse($item->fecha_entrega)->format('Y-m-d');
            })
            ->map(function ($items) {
                $tieneEntregado = $items->contains('estado', 'entregado');
                $tieneAutorizado = $items->contains('estado', 'autorizado');
                
                if ($tieneEntregado && $tieneAutorizado) {
                    return [
                        'class' => 'day-mixed',
                        'title' => 'Entregados y Autorizados'
                    ];
                } elseif ($tieneEntregado) {
                    return [
                        'class' => 'day-entregado',
                        'title' => 'Entregado(s)'
                    ];
                } else {
                    return [
                        'class' => 'day-autorizado',
                        'title' => 'Autorizado(s)'
                    ];
                }
            })->toArray();
    }

    public function rechazarPrestamo(Prestamo $prestamo)
    {
        if ($prestamo->estado !== 'autorizado') {
            return;
        }

        $prestamo->estado = 'rechazado';
        $prestamo->save();

        session()->flash('status', 'Préstamo rechazado y enviado a corrección.');
    }

    public function render()
    {
        $query = Prestamo::query()
            ->with(['cliente', 'representante', 'autorizador'])
            ->whereIn('estado', ['autorizado', 'entregado', 'liquidado']); // Préstamos autorizados, entregados y liquidados

        // Búsqueda por folio o nombre del cliente
        if (! empty($this->search)) {
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
        if (! empty($this->producto)) {
            $query->where('producto', $this->producto);
        }

        // Si hay un grupo especificado, buscamos por ese grupo ignorando fechas
        if (! empty($this->grupo)) {
            $query->where(function ($q) {
                $q->where('id', $this->grupo)
                    ->orWhere('folio', $this->grupo);
            });
        } else {
            // Si no hay grupo, aplicamos filtro por fecha seleccionada
            if (! empty($this->fechaSeleccionada)) {
                $query->whereDate('fecha_entrega', $this->fechaSeleccionada);
            }
        }

        $prestamos = $query->orderByDesc('updated_at')->paginate($this->perPage);

        return view('livewire.prestamos.autorizados', compact('prestamos'));
    }
}
