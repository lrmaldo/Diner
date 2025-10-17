<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Prestamo;
use Livewire\Component;

class Show extends Component
{
    public $prestamoId;

    public $prestamo;

    public $comentarios = '';

    // Simplificación temporal: desactivar historial y estadísticas
    // Mantener propiedades comentadas para evitar referencias en vistas futuras
    // public $historialPrestamos;
    // public $porcentajeCumplimiento = 0;
    // public $totalHistorial = 0;
    // public $estadisticas = [
    //     'total_prestado' => 0,
    //     'total_autorizado' => 0,
    //     'total_rechazado' => 0,
    //     'promedio_monto' => 0,
    //     'prestamos_activos' => 0,
    // ];

    public function mount($id)
    {
        $this->prestamoId = $id;
        $this->loadPrestamo();
    }

    public function loadPrestamo()
    {
    $this->prestamo = Prestamo::with(['clientes', 'cliente', 'representante'])
            ->findOrFail($this->prestamoId);
    }

    // Historial temporalmente deshabilitado para evitar sobrecarga
    // public function loadHistorial()
    // {
    // }

    public function getComportamientoColor($prestamo)
    {
        // Lógica simplificada para determinar el color del comportamiento
        // En el futuro, esto debería basarse en el historial de pagos real
        if ($prestamo->estado === 'autorizado') {
            return 'green';
        } elseif ($prestamo->estado === 'en_comite') {
            return 'orange';
        } elseif ($prestamo->estado === 'rechazado') {
            return 'red';
        }

        return 'gray';
    }

    public function autorizar()
    {
        // Verificar que el usuario tenga permiso
        if (! auth()->user()->can('aprobar prestamos')) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para autorizar préstamos.',
            ]);

            return;
        }

        // Verificar que el préstamo no esté ya autorizado o rechazado
        if ($this->prestamo->estado === 'autorizado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Este préstamo ya ha sido autorizado.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'rechazado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'No se puede autorizar un préstamo rechazado.',
            ]);

            return;
        }

        // Autorizar el préstamo
        $this->prestamo->autorizar(auth()->user());

        // Recargar el préstamo
        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Préstamo autorizado exitosamente.',
        ]);
    }

    public function rechazar(): void
    {
        if (! auth()->user()->can('aprobar prestamos')) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para rechazar préstamos.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'rechazado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'Este préstamo ya ha sido rechazado.',
            ]);

            return;
        }

        if ($this->prestamo->estado === 'autorizado') {
            $this->dispatch('alert', [
                'type' => 'warning',
                'message' => 'No se puede rechazar un préstamo autorizado.',
            ]);

            return;
        }

        $this->prestamo->rechazar(auth()->user());
        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Préstamo rechazado exitosamente.',
        ]);
    }

    public function render()
    {
        return view('livewire.prestamos.show_min');
    }
}
