<?php

namespace App\Livewire\Prestamos;

use App\Models\Cliente;
use App\Models\Prestamo;
use Livewire\Component;

class Show extends Component
{
    public $prestamoId;

    public $prestamo;

    public $historialPrestamos = [];

    public $porcentajeCumplimiento = 0;

    public function mount($id)
    {
        $this->prestamoId = $id;
        $this->loadPrestamo();
        $this->loadHistorial();
    }

    public function loadPrestamo()
    {
        $this->prestamo = Prestamo::with(['cliente', 'clientes', 'representante', 'autorizador'])
            ->findOrFail($this->prestamoId);
    }

    public function loadHistorial()
    {
        // Obtener el historial según el tipo de préstamo
        if ($this->prestamo->producto === 'individual' && $this->prestamo->cliente_id) {
            $clienteId = $this->prestamo->cliente_id;

            // Buscar préstamos anteriores del mismo cliente
            $this->historialPrestamos = Prestamo::where('cliente_id', $clienteId)
                ->where('id', '!=', $this->prestamoId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        } elseif ($this->prestamo->producto === 'grupal' && $this->prestamo->representante_id) {
            $representanteId = $this->prestamo->representante_id;

            // Buscar préstamos anteriores del representante
            $this->historialPrestamos = Prestamo::where('representante_id', $representanteId)
                ->where('id', '!=', $this->prestamoId)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        // Calcular porcentaje de cumplimiento (simulado por ahora)
        if ($this->historialPrestamos->count() > 0) {
            $autorizados = $this->historialPrestamos->where('estado', 'autorizado')->count();
            $this->porcentajeCumplimiento = round(($autorizados / $this->historialPrestamos->count()) * 100);
        }
    }

    public function getComportamientoColor($prestamo)
    {
        // Lógica simplificada para determinar el color del comportamiento
        // En el futuro, esto debería basarse en el historial de pagos real
        if ($prestamo->estado === 'autorizado') {
            return 'green';
        } elseif ($prestamo->estado === 'en_revision') {
            return 'orange';
        } elseif ($prestamo->estado === 'rechazado') {
            return 'red';
        }

        return 'gray';
    }

    public function render()
    {
        return view('livewire.prestamos.show');
    }
}
