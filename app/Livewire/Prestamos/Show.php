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
    public $motivoRechazo = '';

    // Array para almacenar montos autorizados temporalmente
    public $montosAutorizados = [];

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

        // Cargar montos autorizados de la tabla pivot para préstamos grupales
        if ($this->prestamo->producto === 'grupal' && $this->prestamo->clientes) {
            foreach ($this->prestamo->clientes as $cliente) {
                // Por defecto usar null si no hay monto_autorizado aún
                $this->montosAutorizados[$cliente->id] = $cliente->pivot->monto_autorizado ?? null;
            }
        }
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
        // Verificar que el usuario tenga permiso o sea el asesor asignado
        if (! auth()->user()->can('aprobar prestamos') && auth()->id() !== $this->prestamo->asesor_id) {
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
        $this->prestamo->registrarBitacora('autorizado', 'Préstamo autorizado por ' . auth()->user()->name);

        // Recargar el préstamo
        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Préstamo autorizado exitosamente.',
        ]);
    }

    public function rechazar(): void
    {
        if (! auth()->user()->can('aprobar prestamos') && auth()->id() !== $this->prestamo->asesor_id) {
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

        // Requiere motivo para rechazar
        if (empty(trim($this->motivoRechazo))) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Se requiere un motivo para rechazar el préstamo.',
            ]);

            return;
        }

        $this->prestamo->rechazar(auth()->user(), $this->motivoRechazo);
        $this->prestamo->registrarBitacora('rechazado', 'Motivo: ' . $this->motivoRechazo);
        // limpiar motivo después de rechazar
        $this->motivoRechazo = '';
        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Préstamo rechazado exitosamente.',
        ]);
    }

    /**
     * Actualizar el monto autorizado para un cliente específico en préstamos grupales
     */
    public function updateMontoAutorizado(int $clienteId, $monto): void
    {
        // Verificar permisos
        if (! auth()->check() || (! auth()->user()->hasRole('Administrador') && auth()->id() !== $this->prestamo->asesor_id)) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para modificar montos autorizados.',
            ]);

            return;
        }

        // Convertir a float o null, manejando strings vacíos o cero
        $montoAutorizado = null;
        if ($monto !== null && $monto !== '' && (float)$monto > 0) {
            $montoAutorizado = (float) $monto;
        }

        // Actualizar en la tabla pivot
        $this->prestamo->clientes()->updateExistingPivot($clienteId, [
            'monto_autorizado' => $montoAutorizado,
        ]);

        // Actualizar el array local
        $this->montosAutorizados[$clienteId] = $montoAutorizado;

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Monto autorizado actualizado correctamente.',
        ]);
    }

    /**
     * Actualizar el monto autorizado para préstamos individuales
     */
    public function updateMontoAutorizadoIndividual($monto): void
    {
        // Verificar permisos
        if (! auth()->check() || (! auth()->user()->hasRole('Administrador') && auth()->id() !== $this->prestamo->asesor_id)) {
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'No tienes permiso para modificar montos autorizados.',
            ]);

            return;
        }

        // Convertir a float o null, manejando strings vacíos o cero
        $montoAutorizado = null;
        if ($monto !== null && $monto !== '' && (float)$monto > 0) {
            $montoAutorizado = (float) $monto;
        }

        // Para préstamos individuales, guardamos en la tabla pivot también
        if ($this->prestamo->cliente_id) {
            $this->prestamo->clientes()->syncWithoutDetaching([
                $this->prestamo->cliente_id => ['monto_autorizado' => $montoAutorizado],
            ]);
        }

        $this->loadPrestamo();

        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Monto autorizado actualizado correctamente.',
        ]);
    }

    public function render()
    {
        return view('livewire.prestamos.show_min');
    }
}
